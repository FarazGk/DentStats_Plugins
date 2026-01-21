<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

/**
 * Main shortcode for DAT score lookup
 */
function dat_score_lookup_shortcode($atts) {
    $atts = shortcode_atts(array(
        'format' => 'table',
        'show_input' => 'true',
        'placeholder' => 'Enter DAT score (e.g., 15)',
        'button_text' => 'Lookup Scores',
        'error_message' => 'No scores found for the entered value.',
        'success_title' => 'Score Results'
    ), $atts);

    // Check if configuration is valid
    $validation = DAT_Score_Lookup_Functions::validate_configuration();
    if (is_wp_error($validation)) {
        return '<div class="dat-score-error">' . $validation->get_error_message() . '</div>';
    }

    // Category mapping: Display name => database column
    $categories = array(
        'Academic Average' => 'aa',
        'Total Science' => 'sns',
        'PAT' => 'pat',
        'Math' => 'qrt',
        'Reading' => 'rct',
        'Biology' => 'bio',
        'General Chemistry' => 'gch',
        'Organic Chemistry' => 'och'
    );

    ob_start();
    ?>
    <div class="dat-score-converter-container">
        <?php if ($atts['show_input'] === 'true'): ?>
        <div class="dat-score-card" id="dat-score-card">

            <div class="dat-score-card-body">
                <div class="dat-conversion-table-wrapper">
                    <table class="dat-conversion-table">
                        <thead>
                            <tr>
                                <th>DAT Category</th>
                                <th>Old DAT Score (1-30)</th>
                                <th>New DAT Score (200-600)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($categories as $category_name => $category_column): ?>
                            <tr class="dat-conversion-row" data-category="<?php echo esc_attr($category_column); ?>">
                                <td class="dat-category-label"><?php echo esc_html($category_name); ?></td>
                                <td>
                                    <input 
                                        type="text" 
                                        class="dat-score-input dat-score-old" 
                                        data-category="<?php echo esc_attr($category_column); ?>"
                                        data-scale="old"
                                        placeholder=""
                                        maxlength="2"
                                    >
                                </td>
                                <td>
                                    <input 
                                        type="text" 
                                        class="dat-score-input dat-score-new" 
                                        data-category="<?php echo esc_attr($category_column); ?>"
                                        data-scale="new"
                                        placeholder="-"
                                        maxlength="3"
                                    >
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div id="dat-score-error" class="dat-score-error" style="display: none;">
                <p id="dat-error-message"></p>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <script>
    jQuery(document).ready(function($) {
        var tableName = '<?php echo esc_js(DAT_Score_Lookup_Functions::get_configured_table()); ?>';
        var lookupColumn = '<?php echo esc_js(DAT_Score_Lookup_Functions::get_configured_lookup_column()); ?>';
        var conversionTimeouts = {};
        var cachedData = null;
        var dataLoaded = false;

        // Load all table data on page load and cache it
        function loadAllData() {
            if (dataLoaded) {
                return;
            }
            
            $.ajax({
                url: dat_ajax_object.ajax_url,
                method: 'POST',
                data: {
                    action: 'dat_score_load_all_data',
                    table_name: tableName,
                    lookup_column: lookupColumn,
                    nonce: dat_ajax_object.nonce
                },
                success: function(response) {
                    if (response.success) {
                        cachedData = response.data;
                        dataLoaded = true;
                    }
                },
                error: function() {
                    console.error('Failed to load conversion data');
                }
            });
        }

        // Load data immediately
        loadAllData();

        // Helper function to find case-insensitive key in object
        function findKey(obj, searchKey) {
            searchKey = searchKey.toLowerCase();
            for (var key in obj) {
                if (key.toLowerCase() === searchKey) {
                    return key;
                }
            }
            return null;
        }

        // Convert score using cached data
        function convertScoreCached(category, inputValue, direction, $targetInput) {
            if (!cachedData || cachedData.length === 0) {
                $targetInput.val('-');
                return;
            }

            var lookupKey = findKey(cachedData[0], lookupColumn);
            var categoryKey = findKey(cachedData[0], category);

            if (!lookupKey || !categoryKey) {
                $targetInput.val('-');
                return;
            }

            var result = null;

            if (direction === 'old_to_new') {
                // Find exact match in lookup column
                for (var i = 0; i < cachedData.length; i++) {
                    if (String(cachedData[i][lookupKey]) === String(inputValue)) {
                        result = cachedData[i][categoryKey];
                        break;
                    }
                }
            } else if (direction === 'new_to_old') {
                var inputNum = parseFloat(inputValue);
                if (isNaN(inputNum)) {
                    $targetInput.val('-');
                    return;
                }

                // First try exact match
                for (var i = 0; i < cachedData.length; i++) {
                    var categoryValue = parseFloat(cachedData[i][categoryKey]);
                    if (!isNaN(categoryValue) && categoryValue === inputNum) {
                        result = cachedData[i][lookupKey];
                        break;
                    }
                }

                // If no exact match, find closest value
                if (result === null) {
                    var closestValue = null;
                    var minDiff = Infinity;

                    for (var i = 0; i < cachedData.length; i++) {
                        var categoryValue = parseFloat(cachedData[i][categoryKey]);
                        if (!isNaN(categoryValue)) {
                            var diff = Math.abs(categoryValue - inputNum);
                            if (diff < minDiff) {
                                minDiff = diff;
                                closestValue = cachedData[i][lookupKey];
                            }
                        }
                    }

                    result = closestValue;
                }
            }

            // Update the target input with visual feedback
            if (result !== null && result !== undefined && result !== '') {
                $targetInput.val(result);
                highlightUpdate($targetInput);
            } else {
                $targetInput.val('-');
            }
        }

        // Add visual feedback when field is updated
        function highlightUpdate($input) {
            // Remove class first to restart animation if it's already there
            $input.removeClass('dat-score-updated');
            
            // Use requestAnimationFrame to ensure the DOM has updated
            requestAnimationFrame(function() {
                requestAnimationFrame(function() {
                    // Add class to trigger animation
                    $input.addClass('dat-score-updated');
                    
                    // Remove class after animation completes
                    setTimeout(function() {
                        $input.removeClass('dat-score-updated');
                    }, 800);
                });
            });
        }

        // Handle old score input (1-30 scale)
        $(document).on('input', '.dat-score-old', function() {
            var $input = $(this);
            var $row = $input.closest('.dat-conversion-row');
            var $newInput = $row.find('.dat-score-new');
            var category = $input.data('category');
            var value = $input.val().trim();

            // Clear any existing timeout for this input
            if (conversionTimeouts[category + '_old']) {
                clearTimeout(conversionTimeouts[category + '_old']);
            }

            if (!value) {
                $newInput.val('-');
                return;
            }

            // Validate range (1-30)
            var numValue = parseInt(value, 10);
            if (isNaN(numValue) || numValue < 1 || numValue > 30) {
                $newInput.val('-');
                return;
            }

            // Debounce the conversion
            conversionTimeouts[category + '_old'] = setTimeout(function() {
                if (dataLoaded && cachedData) {
                    convertScoreCached(category, value, 'old_to_new', $newInput);
                } else {
                    // Wait for data to load
                    var checkInterval = setInterval(function() {
                        if (dataLoaded && cachedData) {
                            clearInterval(checkInterval);
                            convertScoreCached(category, value, 'old_to_new', $newInput);
                        }
                    }, 100);
                }
            }, 300);
        });

        // Handle new score input (200-600 scale)
        $(document).on('input', '.dat-score-new', function() {
            var $input = $(this);
            var $row = $input.closest('.dat-conversion-row');
            var $oldInput = $row.find('.dat-score-old');
            var category = $input.data('category');
            var value = $input.val().trim();

            // Clear any existing timeout for this input
            if (conversionTimeouts[category + '_new']) {
                clearTimeout(conversionTimeouts[category + '_new']);
            }

            if (!value || value === '-') {
                $oldInput.val('');
                return;
            }

            // Validate range (200-600)
            var numValue = parseInt(value, 10);
            if (isNaN(numValue) || numValue < 200 || numValue > 600) {
                $oldInput.val('');
                return;
            }

            // Debounce the conversion
            conversionTimeouts[category + '_new'] = setTimeout(function() {
                if (dataLoaded && cachedData) {
                    convertScoreCached(category, value, 'new_to_old', $oldInput);
                } else {
                    // Wait for data to load
                    var checkInterval = setInterval(function() {
                        if (dataLoaded && cachedData) {
                            clearInterval(checkInterval);
                            convertScoreCached(category, value, 'new_to_old', $oldInput);
                        }
                    }, 100);
                }
            }, 300);
        });
    });
    </script>
    <?php
    return ob_get_clean();
}
add_shortcode('dat_score_lookup', 'dat_score_lookup_shortcode');

/**
 * Shortcode to display a specific score lookup (no input form)
 */
function dat_score_display_shortcode($atts) {
    $atts = shortcode_atts(array(
        'value' => '',
        'format' => 'table',
        'show_title' => 'true',
        'title' => 'Score Results'
    ), $atts);

    if (empty($atts['value'])) {
        return '<div class="dat-score-error">No lookup value provided.</div>';
    }

    // Check if configuration is valid
    $validation = DAT_Score_Lookup_Functions::validate_configuration();
    if (is_wp_error($validation)) {
        return '<div class="dat-score-error">' . $validation->get_error_message() . '</div>';
    }

    // Perform the lookup
    $data = DAT_Score_Lookup_Functions::lookup_score($atts['value']);
    
    if (is_wp_error($data)) {
        return '<div class="dat-score-error">' . $data->get_error_message() . '</div>';
    }

    ob_start();
    ?>
    <div class="dat-score-converter-container">
        <?php if ($atts['show_title'] === 'true'): ?>
        <h3><?php echo esc_html($atts['title']); ?></h3>
        <?php endif; ?>
        <div class="dat-score-results">
            <?php echo DAT_Score_Lookup_Functions::format_score_display($data, $atts['format']); ?>
        </div>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('dat_score_display', 'dat_score_display_shortcode');

/**
 * Shortcode to show configuration status
 */
function dat_score_config_shortcode($atts) {
    $atts = shortcode_atts(array(
        'show_status' => 'true',
        'show_table' => 'true',
        'show_column' => 'true'
    ), $atts);

    $validation = DAT_Score_Lookup_Functions::validate_configuration();
    $is_valid = !is_wp_error($validation);

    ob_start();
    ?>
    <div class="dat-score-config-status">
        <?php if ($atts['show_status'] === 'true'): ?>
        <p><strong>Configuration Status:</strong> 
            <span class="<?php echo $is_valid ? 'status-valid' : 'status-invalid'; ?>">
                <?php echo $is_valid ? 'Valid' : 'Invalid'; ?>
            </span>
        </p>
        <?php endif; ?>

        <?php if ($is_valid): ?>
            <?php if ($atts['show_table'] === 'true'): ?>
            <p><strong>Table:</strong> <?php echo esc_html(DAT_Score_Lookup_Functions::get_configured_table()); ?></p>
            <?php endif; ?>
            
            <?php if ($atts['show_column'] === 'true'): ?>
            <p><strong>Lookup Column:</strong> <?php echo esc_html(DAT_Score_Lookup_Functions::get_configured_lookup_column()); ?></p>
            <?php endif; ?>
        <?php else: ?>
            <p class="error"><?php echo esc_html($validation->get_error_message()); ?></p>
        <?php endif; ?>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('dat_score_config', 'dat_score_config_shortcode');

/**
 * Debug shortcode to show current configuration
 */
function dat_score_debug_shortcode($atts) {
    $table_name = get_option('dat_score_converter_table', '');
    $lookup_column = get_option('dat_score_converter_lookup_column', '');
    
    ob_start();
    ?>
    <div class="dat-score-debug">
        <h3>DAT Score Converter Debug Info</h3>
        <p><strong>Table Name:</strong> <?php echo esc_html($table_name); ?></p>
        <p><strong>Lookup Column:</strong> <?php echo esc_html($lookup_column); ?></p>
        <p><strong>Table Name Length:</strong> <?php echo strlen($table_name); ?></p>
        <p><strong>Table Name Characters:</strong> <?php echo implode(' ', str_split($table_name)); ?></p>
        <p><strong>Regex Test:</strong> <?php echo preg_match('/^[a-zA-Z0-9_\-\.]+$/', $table_name) ? 'PASS' : 'FAIL'; ?></p>
    </div>
    <?php
    return ob_get_clean();
}
add_shortcode('dat_score_debug', 'dat_score_debug_shortcode');
