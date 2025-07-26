jQuery(document).ready(function($) {
    const voteBtn = $('#roadmap-vote-btn');
    const voteCount = voteBtn.find('.roadmap-single-vote-count');
    const voteText = voteBtn.find('.roadmap-single-vote-text');
    const notification = $('#roadmap-notification');
    let isVoting = false;
    
    // Show notification
    function showNotification(message, type = 'success') {
        notification.text(message);
        notification.removeClass('error').addClass(type === 'error' ? 'error' : '');
        notification.addClass('show');
        
        setTimeout(function() {
            notification.removeClass('show');
        }, 3000);
    }
    
    // Handle vote click
    voteBtn.on('click', function(e) {
        e.preventDefault();
        
        if (isVoting) return;
        
        // Check if user is logged in
        if (!roadmap_ajax.nonce) {
            showNotification(roadmap_ajax.i18n.login_required, 'error');
            return;
        }
        
        isVoting = true;
        voteBtn.prop('disabled', true);
        voteBtn.addClass('voting');
        
        const isVoted = voteBtn.hasClass('voted');
        const voteValue = isVoted ? 0 : 1;
        
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
                vote_value: voteValue
            }),
            success: function(response) {
                // Update UI
                if (voteValue === 0) {
                    voteBtn.removeClass('voted');
                    voteText.text('Vote');
                    showNotification(roadmap_ajax.i18n.vote_removed);
                } else {
                    voteBtn.addClass('voted');
                    voteText.text('Voted');
                    showNotification(roadmap_ajax.i18n.vote_submitted);
                }
                
                // Update vote count with animation
                const newCount = response.vote_count;
                const currentCount = parseInt(voteCount.text());
                
                if (newCount !== currentCount) {
                    voteCount.fadeOut(200, function() {
                        $(this).text(newCount).fadeIn(200);
                    });
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
                voteBtn.prop('disabled', false);
                voteBtn.removeClass('voting');
            }
        });
    });
});