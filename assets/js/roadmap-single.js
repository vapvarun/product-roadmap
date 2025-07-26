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
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) scale(0.8);
            padding: 20px 30px;
            border-radius: 8px;
            background: #fff;
            color: #333;
            z-index: 99999;
            opacity: 0;
            transition: all 0.3s ease;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
            font-size: 16px;
            font-weight: 500;
            text-align: center;
            min-width: 250px;
        }
        
        .roadmap-notification.show {
            opacity: 1;
            transform: translate(-50%, -50%) scale(1);
        }
        
        .roadmap-notification-success {
            background: #fff;
            color: #4caf50;
            border: 2px solid #4caf50;
        }
        
        .roadmap-notification-success::before {
            content: "✓ ";
            font-size: 20px;
            font-weight: bold;
            margin-right: 8px;
        }
        
        .roadmap-notification-error {
            background: #fff;
            color: #f44336;
            border: 2px solid #f44336;
        }
        
        .roadmap-notification-error::before {
            content: "✕ ";
            font-size: 20px;
            font-weight: bold;
            margin-right: 8px;
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