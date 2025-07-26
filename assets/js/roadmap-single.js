jQuery(document).ready(function($) {
    'use strict';
    
    // Vote button handler
    $('.roadmap-vote-btn').on('click', function(e) {
        e.preventDefault();
        
        var $button = $(this);
        var itemId = $button.data('item-id');
        var hasVoted = $button.hasClass('voted');
        
        // Disable button during request
        $button.prop('disabled', true);
        
        $.ajax({
            url: roadmapAjax.apiUrl + '/vote',
            method: 'POST',
            beforeSend: function(xhr) {
                xhr.setRequestHeader('X-WP-Nonce', roadmapAjax.nonce);
            },
            data: {
                item_id: itemId,
                vote_value: hasVoted ? 0 : 1 // 0 to remove vote, 1 to add vote
            },
            success: function(response) {
                if (response.success) {
                    // Update vote count
                    var $voteCount = $('.roadmap-votes');
                    var currentVotes = parseInt($voteCount.text()) || 0;
                    
                    if (hasVoted) {
                        // Removed vote
                        $button.removeClass('voted');
                        $button.text('Vote for this feature');
                        $voteCount.text((currentVotes - 1) + ' votes');
                    } else {
                        // Added vote
                        $button.addClass('voted');
                        $button.text('Remove vote');
                        $voteCount.text((currentVotes + 1) + ' votes');
                    }
                    
                    // Show success message
                    showNotification(response.message || 'Vote updated successfully', 'success');
                } else {
                    showNotification(response.message || 'Failed to update vote', 'error');
                }
            },
            error: function(xhr) {
                var message = 'An error occurred';
                if (xhr.status === 401) {
                    message = 'Please login to vote';
                }
                showNotification(message, 'error');
            },
            complete: function() {
                $button.prop('disabled', false);
            }
        });
    });
    
    function showNotification(message, type) {
        var $notification = $('<div class="roadmap-notification roadmap-notification-' + type + '">' + message + '</div>');
        
        $('body').append($notification);
        
        // Fade in
        setTimeout(function() {
            $notification.addClass('show');
        }, 10);
        
        // Remove after 3 seconds
        setTimeout(function() {
            $notification.removeClass('show');
            setTimeout(function() {
                $notification.remove();
            }, 300);
        }, 3000);
    }
});

// Add notification styles
(function() {
    var style = document.createElement('style');
    style.textContent = `
        .roadmap-notification {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 4px;
            background: #333;
            color: white;
            z-index: 9999;
            opacity: 0;
            transform: translateX(100%);
            transition: all 0.3s ease;
            max-width: 300px;
        }
        
        .roadmap-notification.show {
            opacity: 1;
            transform: translateX(0);
        }
        
        .roadmap-notification-success {
            background: #4caf50;
        }
        
        .roadmap-notification-error {
            background: #f44336;
        }
        
        .roadmap-vote-btn.voted {
            background: #666;
        }
        
        .roadmap-vote-btn.voted:hover {
            background: #555;
        }
        
        .roadmap-vote-btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }
    `;
    document.head.appendChild(style);
})();