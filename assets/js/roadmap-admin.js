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
            var $form = $('#product-settings-form');

            $('#product_id').val(productId);
            $('#product-settings-modal').css('display', 'block');

            // Reset color to the field default while we fetch; avoids showing
            // the previously-open product's color for a flash.
            $('#product_theme_color').val('#2271b1');

            // Load the saved settings for this product so the modal reflects
            // what is actually stored, not the hardcoded HTML default.
            $.post(roadmap_admin.ajax_url, {
                action: 'roadmap_get_product_settings',
                product_id: productId,
                nonce: $('#roadmap_nonce').val()
            }).done(function(response) {
                if (response && response.success && response.data) {
                    if (response.data.theme_color) {
                        $('#product_theme_color').val(response.data.theme_color);
                    }
                    if (response.data.default_status) {
                        $('#product_default_status').val(response.data.default_status);
                    }
                }
            });
        };

        window.closeProductModal = function() {
            $('#product-settings-modal').css('display', 'none');
        };

        // Persist product settings via AJAX. The endpoint (wp_ajax_roadmap_save_product_settings)
        // already existed but was never called from JS.
        $('#product-settings-form').on('submit', function(e) {
            e.preventDefault();

            var $form   = $(this);
            var $button = $form.find('button[type="submit"]');
            var originalText = $button.text();

            $button.prop('disabled', true);

            $.post(roadmap_admin.ajax_url, $form.serialize() + '&action=roadmap_save_product_settings')
                .done(function(response) {
                    if (response && response.success) {
                        closeProductModal();
                    } else {
                        window.alert(roadmap_admin.save_error || 'Error saving settings. Please try again.');
                    }
                })
                .fail(function() {
                    window.alert(roadmap_admin.save_error || 'Error saving settings. Please try again.');
                })
                .always(function() {
                    $button.prop('disabled', false).text(originalText);
                });
        });

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

        // NOTE: the Dashboard demo-import binding was removed in 1.8.0. It
        // listened on .roadmap-import-demo and posted roadmap_import_demo_data,
        // and neither existed: no admin template rendered that class and no
        // handler was registered for that action. Dead JS that looks like a
        // working feature is how someone concludes demo import is broken rather
        // than absent. Re-importing demo content from the Dashboard can be
        // built deliberately - it needs markup, a handler, and the same guard
        // that stops the setup wizard importing twice.

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