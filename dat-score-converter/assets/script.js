/**
 * DAT Score Converter JavaScript
 * Handles frontend input validation
 */

jQuery(document).ready(function($) {
    'use strict';

    // Add input validation for score inputs
    $(document).on('input', '.dat-score-input', function() {
        var $input = $(this);
        var value = $input.val().trim();
        var isOldScore = $input.hasClass('dat-score-old');
        var isNewScore = $input.hasClass('dat-score-new');
        
        // Remove any previous validation classes
        $input.removeClass('valid invalid');
        
        if (value === '' || value === '-') {
            return; // Don't validate empty fields
        }
        
        // Validate based on score type
        if (isOldScore) {
            // Old score: 1-30
            var numValue = parseInt(value, 10);
            if (!isNaN(numValue) && numValue >= 1 && numValue <= 30) {
                $input.addClass('valid');
            } else {
                $input.addClass('invalid');
            }
        } else if (isNewScore) {
            // New score: 200-600
            var numValue = parseInt(value, 10);
            if (!isNaN(numValue) && numValue >= 200 && numValue <= 600) {
                $input.addClass('valid');
            } else {
                $input.addClass('invalid');
            }
        }
    });

    // Add visual feedback for validation
    $('<style>')
        .prop('type', 'text/css')
        .html(`
            .dat-score-input.valid {
                border-color: #28a745;
                box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.1);
            }
            .dat-score-input.invalid {
                border-color: #dc3545;
                box-shadow: 0 0 0 3px rgba(220, 53, 69, 0.1);
            }
        `)
        .appendTo('head');

    // Escape key to clear error messages
    $(document).on('keydown', function(e) {
        if (e.which === 27) { // Escape key
            $('.dat-score-error').hide();
        }
    });
});

