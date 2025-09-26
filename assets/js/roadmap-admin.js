/**
 * Product Roadmap Admin JavaScript
 */
(function($) {
    'use strict';

    $(document).ready(function() {
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