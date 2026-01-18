/**
 * Select2 initialization for changelog/roadmap product filters
 */
(function($) {
    'use strict';

    $(document).ready(function() {
        // Custom template for option with count badge
        function roadmapFormatProduct(product) {
            if (!product.id) {
                return product.text;
            }

            var count = $(product.element).data('count');
            var $option = $('<span class="roadmap-plugin-option">' +
                '<span class="roadmap-plugin-option-name">' + product.text + '</span>' +
                (count ? '<span class="roadmap-plugin-option-count">' + count + '</span>' : '') +
                '</span>');

            return $option;
        }

        // Initialize Select2 on changelog product select
        if ($('#roadmap-changelog-product-select').length) {
            $('#roadmap-changelog-product-select').select2({
                theme: 'roadmap-plugin',
                placeholder: roadmapSelect2.placeholder || 'Search products...',
                allowClear: false,
                minimumResultsForSearch: 0,
                templateResult: roadmapFormatProduct,
                dropdownAutoWidth: false,
                width: 'resolve'
            });

            // Navigate on selection
            $('#roadmap-changelog-product-select').on('select2:select', function(e) {
                var url = e.params.data.id;
                if (url) {
                    window.location.href = url;
                }
            });
        }

        // Initialize Select2 on roadmap items product select
        if ($('#roadmap-items-product-select').length) {
            $('#roadmap-items-product-select').select2({
                theme: 'roadmap-plugin',
                placeholder: roadmapSelect2.placeholder || 'Search products...',
                allowClear: false,
                minimumResultsForSearch: 0,
                templateResult: roadmapFormatProduct,
                dropdownAutoWidth: false,
                width: 'resolve'
            });

            // Navigate on selection
            $('#roadmap-items-product-select').on('select2:select', function(e) {
                var url = e.params.data.id;
                if (url) {
                    window.location.href = url;
                }
            });
        }
    });
})(jQuery);
