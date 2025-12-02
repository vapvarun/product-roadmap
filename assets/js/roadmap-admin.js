/**
 * Product Roadmap Admin JavaScript
 */
(function($) {
    'use strict';

    $(document).ready(function() {
        // Copy shortcode functionality - supports both old and new button classes
        $(document).on('click', '.copy-shortcode, .copy-shortcode-btn', function(e) {
            e.preventDefault();
            var button = $(this);
            var shortcode = button.data('shortcode');
            var originalText = button.find('.copy-text').text();
            var hasText = button.find('.copy-text').length > 0;
            var iconClass = button.find('.dashicons').hasClass('dashicons-admin-page') ? 'dashicons-admin-page' : 'dashicons-clipboard';

            // Create temporary textarea for copying
            var tempInput = $('<textarea>');
            $('body').append(tempInput);
            tempInput.val(shortcode).select();

            function showSuccess() {
                button.addClass('copied');
                if (hasText) {
                    button.find('.copy-text').text('Copied!');
                }
                button.find('.dashicons').removeClass(iconClass).addClass('dashicons-yes');

                // Reset after 2 seconds
                setTimeout(function() {
                    button.removeClass('copied');
                    if (hasText) {
                        button.find('.copy-text').text(originalText);
                    }
                    button.find('.dashicons').removeClass('dashicons-yes').addClass(iconClass);
                }, 2000);
            }

            try {
                // Try modern clipboard API first
                if (navigator.clipboard && window.isSecureContext) {
                    navigator.clipboard.writeText(shortcode).then(function() {
                        showSuccess();
                    });
                } else {
                    // Fallback for older browsers
                    document.execCommand('copy');
                    showSuccess();
                }
            } catch (err) {
                console.error('Failed to copy:', err);
                alert('Failed to copy shortcode. Please select and copy manually.');
            } finally {
                tempInput.remove();
            }
        });
        // Dismiss setup notice
        $('.roadmap-dismiss-setup-notice').on('click', function(e) {
            e.preventDefault();
            var nonce = $(this).data('nonce');
            $.post(ajaxurl, {
                action: 'roadmap_dismiss_setup_notice',
                nonce: nonce
            });
            $('#roadmap-setup-notice').fadeOut();
        });
        // Settings saved notification
        if ($(".roadmap-settings-saved").length) {
            $(".roadmap-settings-saved").show();
            setTimeout(function() {
                $(".roadmap-settings-saved").fadeOut();
            }, 3000);
        }

        // Product settings modal functions
        window.editProductSettings = function(productId) {
            $('#product_id').val(productId);
            $('#product-settings-modal').css('display', 'block');

            // Load existing settings via AJAX if needed
            // This is a placeholder for future AJAX implementation
        };

        window.closeProductModal = function() {
            $('#product-settings-modal').css('display', 'none');
        };

        // Attach event listener to close button
        $('.roadmap-modal-close').on('click', function() {
            closeProductModal();
        });

        // Close modal when clicking outside of it
        $(window).on('click', function(e) {
            if ($(e.target).is('#product-settings-modal')) {
                closeProductModal();
            }
        });

        // Demo import functionality
        $('.roadmap-import-demo').on('click', function(e) {
            e.preventDefault();

            if (!confirm(roadmap_admin.confirm_demo_import)) {
                return;
            }

            var button = $(this);
            button.prop('disabled', true).text(roadmap_admin.importing_text);

            $.post(ajaxurl, {
                action: 'roadmap_import_demo_data',
                nonce: roadmap_admin.nonce
            }, function(response) {
                if (response.success) {
                    alert(roadmap_admin.import_success);
                    location.reload();
                } else {
                    alert(response.data || roadmap_admin.import_error);
                    button.prop('disabled', false).text(roadmap_admin.import_demo_text);
                }
            }).fail(function() {
                alert(roadmap_admin.import_error);
                button.prop('disabled', false).text(roadmap_admin.import_demo_text);
            });
        });

        // CPT admin functionality
        function initRoadmapAdmin() {
            // Toggle meta box sections
            $('.roadmap-meta-toggle').on('click', function() {
                var target = $(this).data('target');
                $('#' + target).slideToggle();
                $(this).toggleClass('active');
            });

            // Color picker initialization
            if ($.fn.wpColorPicker) {
                $('.roadmap-color-picker').wpColorPicker();
            }

            // Settings tab navigation - removed as tabs use regular links
            // The PHP handles tab switching via URL parameters
        }

        // Initialize on page load
        initRoadmapAdmin();

        // Reinitialize after AJAX operations
        $(document).on('ajaxComplete', function() {
            initRoadmapAdmin();
        });
    });

})(jQuery);