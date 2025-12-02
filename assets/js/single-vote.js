/**
 * Single Roadmap Item - Vote Functionality
 *
 * Handles upvote/downvote with StackOverflow-style UI
 *
 * @package ProductRoadmap
 * @since 1.3.0
 */
jQuery(document).ready(function($) {
    const upBtn = $('.roadmap-vote-up');
    const downBtn = $('.roadmap-vote-down');
    const voteCountEl = $('#vote-count');
    const voteLabelEl = $('.roadmap-vote-label');
    const notification = $('#roadmap-notification');
    let isVoting = false;
    let currentUserVote = roadmap_ajax.user_vote || 0;

    /**
     * Show notification toast
     */
    function showNotification(message, type = 'success') {
        notification.text(message);
        notification.removeClass('error success').addClass(type);
        notification.addClass('show');

        setTimeout(function() {
            notification.removeClass('show');
        }, 2500);
    }

    /**
     * Update vote count with animation
     */
    function updateVoteCount(newCount) {
        const currentCount = parseInt(voteCountEl.text());

        if (newCount !== currentCount) {
            voteCountEl.css('transform', 'scale(1.2)');
            setTimeout(function() {
                voteCountEl.text(newCount);
                voteCountEl.css('transform', 'scale(1)');
            }, 150);
        }

        // Update label (vote/votes)
        const label = Math.abs(newCount) === 1 ? 'vote' : 'votes';
        voteLabelEl.text(label);
    }

    /**
     * Update button states
     */
    function updateButtonStates(userVote) {
        upBtn.removeClass('active');
        downBtn.removeClass('active');

        if (userVote > 0) {
            upBtn.addClass('active');
        } else if (userVote < 0) {
            downBtn.addClass('active');
        }

        currentUserVote = userVote;
    }

    /**
     * Handle vote action
     */
    function handleVote(action) {
        if (isVoting) return;

        // Check if user is logged in
        if (!roadmap_ajax.is_logged_in) {
            showNotification(roadmap_ajax.i18n.login_required, 'error');
            // Redirect to login after delay
            setTimeout(function() {
                window.location.href = roadmap_ajax.login_url;
            }, 1500);
            return;
        }

        isVoting = true;
        upBtn.prop('disabled', true).addClass('voting');
        downBtn.prop('disabled', true).addClass('voting');

        // Determine new vote value
        let newVoteValue = 0;

        if (action === 'upvote') {
            // If already upvoted, remove vote; otherwise upvote
            newVoteValue = currentUserVote > 0 ? 0 : 1;
        } else if (action === 'downvote') {
            // Down arrow only removes an existing upvote, doesn't go negative
            if (currentUserVote > 0) {
                newVoteValue = 0; // Remove upvote
            } else {
                // Already at 0 or below, do nothing
                isVoting = false;
                upBtn.prop('disabled', false).removeClass('voting');
                downBtn.prop('disabled', false).removeClass('voting');
                return;
            }
        }

        // Make API call
        $.ajax({
            url: roadmap_ajax.api_url + '/vote',
            method: 'POST',
            headers: {
                'X-WP-Nonce': roadmap_ajax.nonce,
                'Content-Type': 'application/json'
            },
            data: JSON.stringify({
                item_id: roadmap_ajax.item_id,
                vote_value: newVoteValue
            }),
            success: function(response) {
                // Update UI
                updateButtonStates(newVoteValue);
                updateVoteCount(response.vote_count);

                // Show notification
                if (newVoteValue === 0) {
                    showNotification(roadmap_ajax.i18n.vote_removed);
                } else {
                    showNotification(roadmap_ajax.i18n.vote_submitted);
                }
            },
            error: function(xhr) {
                let errorMsg = roadmap_ajax.i18n.error;
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                showNotification(errorMsg, 'error');
            },
            complete: function() {
                isVoting = false;
                upBtn.prop('disabled', false).removeClass('voting');
                downBtn.prop('disabled', false).removeClass('voting');
            }
        });
    }

    // Bind click events
    upBtn.on('click', function(e) {
        e.preventDefault();
        handleVote('upvote');
    });

    downBtn.on('click', function(e) {
        e.preventDefault();
        handleVote('downvote');
    });

    // Keyboard accessibility
    upBtn.on('keypress', function(e) {
        if (e.which === 13 || e.which === 32) {
            e.preventDefault();
            handleVote('upvote');
        }
    });

    downBtn.on('keypress', function(e) {
        if (e.which === 13 || e.which === 32) {
            e.preventDefault();
            handleVote('downvote');
        }
    });

    // Initialize button states from server data
    updateButtonStates(currentUserVote);
});
