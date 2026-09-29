/**
 * Product Roadmap Meta Box JavaScript
 */
(function($) {
    'use strict';

    $(document).ready(function() {
        // Initialize range sliders
        $('.roadmap-range-input').each(function() {
            var $input = $(this);
            var $value = $input.next('.roadmap-range-value');

            // Set initial value
            if ($value.length) {
                $value.text($input.val() + '%');
            }

            // Update on input change
            $input.on('input', function() {
                $value.text($(this).val() + '%');
            });
        });

        // Initialize date pickers if available
        if ($.fn.datepicker) {
            $('.roadmap-date-input').datepicker({
                dateFormat: 'yy-mm-dd',
                changeMonth: true,
                changeYear: true
            });
        }

        // Toggle meta box sections
        $('.roadmap-meta-toggle').on('click', function() {
            var target = $(this).data('target');
            $('#' + target).slideToggle();
            $(this).toggleClass('active');
        });

    });

})(jQuery);