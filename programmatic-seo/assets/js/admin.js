/**
 * Programmatic SEO Admin Scripts
 */

(function($) {
    'use strict';

    $(document).ready(function() {
        // Form submission handler
        $('form').on('submit', function(e) {
            var checkedFeatures = 0;

            $('input[type="checkbox"]:checked').each(function() {
                checkedFeatures++;
            });

            if (checkedFeatures === 0) {
                var message = 'No features are enabled. Are you sure you want to continue?';
                if (!confirm(message)) {
                    e.preventDefault();
                }
            }
        });

        // Highlight active form fields
        $('input, textarea, select').on('focus', function() {
            $(this).closest('tr').addClass('active');
        }).on('blur', function() {
            $(this).closest('tr').removeClass('active');
        });

        // Add visual feedback for checkboxes
        $('input[type="checkbox"]').on('change', function() {
            if ($(this).is(':checked')) {
                $(this).closest('td').addClass('enabled');
            } else {
                $(this).closest('td').removeClass('enabled');
            }
        }).trigger('change');
    });

})(jQuery);
