<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('DAT_Score_Converter_Admin')) {

    class DAT_Score_Converter_Admin {

        public function __construct() {
            add_action('admin_menu', array($this, 'add_admin_menu'));
        }

        public function add_admin_menu() {
            add_menu_page(
                'DAT Score Converter',
                'DAT Score Converter',
                'manage_options',
                'dat-score-converter',
                array($this, 'create_admin_page'),
                'dashicons-calculator',
                25
            );
        }

        public function create_admin_page() {
            global $wpdb;
            
            // Get all custom tables (excluding WordPress core tables)
            $tables = $this->get_available_tables();
            
            // Get current configuration
            $current_table = get_option('dat_score_converter_table', '');
            $current_column = get_option('dat_score_converter_lookup_column', '');
            ?>
            <div class="wrap">
                <h1>DAT Score Converter</h1>
                
                <div class="dat-admin-container">
                    <?php if ($current_table && $current_column): ?>
                    <div class="dat-admin-section" style="background: #d4edda; border-color: #c3e6cb;">
                        <h2>Current Configuration</h2>
                        <p><strong>Table:</strong> <?php echo esc_html($current_table); ?></p>
                        <p><strong>Lookup Column:</strong> <?php echo esc_html($current_column); ?></p>
                        <p class="description">Configuration is saved and ready to use.</p>
                    </div>
                    <?php endif; ?>
                    
                    <div class="dat-admin-section">
                        <h2>Available Lookup Tables</h2>
                        <p>Select a table to configure lookup settings and view available data.</p>
                        
                        <form id="table-selection-form">
                            <table class="form-table">
                                <tr>
                                    <th scope="row">
                                        <label for="selected_table">Select Table</label>
                                    </th>
                                    <td>
                                        <select id="selected_table" name="selected_table" class="regular-text">
                                            <option value="">-- Select a table --</option>
                                            <?php foreach ($tables as $table): ?>
                                                <option value="<?php echo esc_attr($table); ?>" <?php selected($current_table, $table); ?>>
                                                    <?php echo esc_html($table); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <p class="description">Choose a table created by the CSV-to-SQL plugin.</p>
                                    </td>
                                </tr>
                            </table>
                        </form>
                    </div>

                    <div id="table-info-section" class="dat-admin-section" style="display: none;">
                        <h2>Table Information</h2>
                        <div id="table-info-content">
                            <!-- Table information will be loaded here via AJAX -->
                        </div>
                    </div>

                    <div id="lookup-config-section" class="dat-admin-section" style="display: none;">
                        <h2>Lookup Configuration</h2>
                        <div id="lookup-config-content">
                            <!-- Lookup configuration will be loaded here -->
                        </div>
                    </div>

                    <div id="test-lookup-section" class="dat-admin-section" style="display: none;">
                        <h2>Test Lookup</h2>
                        <div id="test-lookup-content">
                            <!-- Test lookup interface will be loaded here -->
                        </div>
                    </div>
                </div>

                <div class="dat-admin-section">
                    <h2>Shortcode Usage</h2>
                    <p>Use the following shortcode to display the lookup interface on your pages:</p>
                    <code>[dat_score_lookup]</code>
                    <p class="description">The shortcode will automatically use the currently selected table and configuration.</p>
                </div>
            </div>

            <style>
                .dat-admin-container {
                    max-width: 1200px;
                }
                .dat-admin-section {
                    background: #fff;
                    border: 1px solid #ccd0d4;
                    border-radius: 4px;
                    padding: 20px;
                    margin: 20px 0;
                    box-shadow: 0 1px 1px rgba(0,0,0,.04);
                }
                .dat-admin-section h2 {
                    margin-top: 0;
                    color: #23282d;
                }
                .table-preview {
                    max-height: 300px;
                    overflow-y: auto;
                    border: 1px solid #ddd;
                    margin-top: 10px;
                }
                .table-preview table {
                    width: 100%;
                    border-collapse: collapse;
                }
                .table-preview th,
                .table-preview td {
                    border: 1px solid #ddd;
                    padding: 8px;
                    text-align: left;
                }
                .table-preview th {
                    background-color: #f1f1f1;
                    font-weight: bold;
                }
                .lookup-test-form {
                    background: #f9f9f9;
                    padding: 15px;
                    border-radius: 4px;
                    margin-top: 15px;
                }
                .lookup-result {
                    margin-top: 15px;
                    padding: 15px;
                    border-radius: 4px;
                }
                .lookup-result.success {
                    background: #d4edda;
                    border: 1px solid #c3e6cb;
                    color: #155724;
                }
                .lookup-result.error {
                    background: #f8d7da;
                    border: 1px solid #f5c6cb;
                    color: #721c24;
                }
            </style>

            <script>
            jQuery(document).ready(function($) {
                // Load configuration on page load
                var currentTable = '<?php echo esc_js($current_table); ?>';
                if (currentTable) {
                    $('#selected_table').val(currentTable).trigger('change');
                }
                
                $('#selected_table').on('change', function() {
                    var tableName = $(this).val();
                    
                    if (tableName) {
                        // Show table info section
                        $('#table-info-section').show();
                        $('#lookup-config-section').show();
                        $('#test-lookup-section').show();
                        
                        // Load table information
                        loadTableInfo(tableName);
                    } else {
                        $('#table-info-section').hide();
                        $('#lookup-config-section').hide();
                        $('#test-lookup-section').hide();
                    }
                });

                function loadTableInfo(tableName) {
                    console.log('Loading table info for: ' + tableName);
                    $.ajax({
                        url: ajaxurl,
                        method: 'POST',
                        data: {
                            action: 'dat_get_table_columns',
                            table_name: tableName,
                            nonce: '<?php echo wp_create_nonce('dat_score_converter_nonce'); ?>'
                        },
                        success: function(response) {
                            console.log('Response:', response);
                            if (response.success) {
                                displayTableInfo(tableName, response.data);
                            } else {
                                $('#table-info-content').html('<p class="error">Error: ' + response.data + '</p>');
                            }
                        },
                        error: function(xhr, status, error) {
                            console.log('AJAX Error:', xhr, status, error);
                            $('#table-info-content').html('<p class="error">Error loading table information: ' + error + '</p>');
                        }
                    });
                }

                function displayTableInfo(tableName, columns) {
                    var html = '<p><strong>Table:</strong> ' + tableName + '</p>';
                    html += '<p><strong>Columns:</strong> ' + columns.join(', ') + '</p>';
                    
                    // Create lookup configuration
                    var configHtml = '<form id="lookup-config-form">';
                    configHtml += '<table class="form-table">';
                    configHtml += '<tr><th scope="row"><label for="lookup_column">Lookup Column</label></th>';
                    configHtml += '<td><select id="lookup_column" name="lookup_column" class="regular-text">';
                    configHtml += '<option value="">-- Select lookup column --</option>';
                    
                    columns.forEach(function(column) {
                        if (column !== 'id') { // Exclude auto-increment ID
                            var selected = (column === '<?php echo esc_js($current_column); ?>') ? ' selected' : '';
                            configHtml += '<option value="' + column + '"' + selected + '>' + column + '</option>';
                        }
                    });
                    
                    configHtml += '</select>';
                    configHtml += '<p class="description">Select the column to use for lookups (e.g., "dat" for DAT scores).</p>';
                    configHtml += '</td></tr>';
                    configHtml += '</table>';
                    configHtml += '<p class="submit">';
                    configHtml += '<button type="button" id="save-config-btn" class="button button-primary">Save Configuration</button>';
                    configHtml += '</p>';
                    configHtml += '</form>';
                    
                    // Create test lookup interface
                    var testHtml = '<div class="lookup-test-form">';
                    testHtml += '<h3>Test Lookup</h3>';
                    testHtml += '<form id="test-lookup-form">';
                    testHtml += '<input type="hidden" id="test_table_name" value="' + tableName + '">';
                    testHtml += '<p><label for="test_lookup_value">Enter lookup value:</label></p>';
                    testHtml += '<p><input type="text" id="test_lookup_value" class="regular-text" placeholder="e.g., 15"></p>';
                    testHtml += '<p><button type="button" id="test-lookup-btn" class="button button-primary">Test Lookup</button></p>';
                    testHtml += '</form>';
                    testHtml += '<div id="test-lookup-result"></div>';
                    testHtml += '</div>';
                    
                    $('#table-info-content').html(html);
                    $('#lookup-config-content').html(configHtml);
                    $('#test-lookup-content').html(testHtml);
                    
                    // Handle save configuration
                    $('#save-config-btn').on('click', function() {
                        var selectedTableName = tableName;
                        var lookupColumn = $('#lookup_column').val();
                        
                        if (!lookupColumn) {
                            alert('Please select a lookup column first.');
                            return;
                        }
                        
                        $.ajax({
                            url: ajaxurl,
                            method: 'POST',
                            data: {
                                action: 'dat_save_configuration',
                                table_name: selectedTableName,
                                lookup_column: lookupColumn,
                                nonce: '<?php echo wp_create_nonce('dat_score_converter_nonce'); ?>'
                            },
                            success: function(response) {
                                if (response.success) {
                                    alert('Configuration saved successfully!');
                                } else {
                                    alert('Error saving configuration: ' + response.data);
                                }
                            },
                            error: function() {
                                alert('Error saving configuration.');
                            }
                        });
                    });
                    
                    // Handle test lookup
                    $('#test-lookup-btn').on('click', function() {
                        var tableName = $('#test_table_name').val();
                        var lookupColumn = $('#lookup_column').val();
                        var lookupValue = $('#test_lookup_value').val();
                        
                        if (!lookupColumn) {
                            alert('Please select a lookup column first.');
                            return;
                        }
                        
                        if (!lookupValue) {
                            alert('Please enter a lookup value.');
                            return;
                        }
                        
                        $.ajax({
                            url: ajaxurl,
                            method: 'POST',
                            data: {
                                action: 'dat_score_lookup',
                                table_name: tableName,
                                lookup_column: lookupColumn,
                                lookup_value: lookupValue,
                                nonce: '<?php echo wp_create_nonce('dat_score_converter_nonce'); ?>'
                            },
                            success: function(response) {
                                if (response.success) {
                                    displayLookupResult(response.data, true);
                                } else {
                                    displayLookupResult(response.data, false);
                                }
                            },
                            error: function() {
                                displayLookupResult('Error performing lookup.', false);
                            }
                        });
                    });
                }

                function displayLookupResult(data, success) {
                    var resultDiv = $('#test-lookup-result');
                    var className = success ? 'success' : 'error';
                    
                    if (success && typeof data === 'object') {
                        var html = '<div class="lookup-result ' + className + '">';
                        html += '<h4>Lookup Result:</h4>';
                        html += '<table class="widefat">';
                        for (var key in data) {
                            html += '<tr><td><strong>' + key + ':</strong></td><td>' + data[key] + '</td></tr>';
                        }
                        html += '</table>';
                        html += '</div>';
                        resultDiv.html(html);
                    } else {
                        resultDiv.html('<div class="lookup-result ' + className + '">' + data + '</div>');
                    }
                }
            });
            </script>
            <?php
        }

        private function get_available_tables() {
            global $wpdb;
            
            // Get all tables in the database
            $tables = $wpdb->get_results("SHOW TABLES", ARRAY_N);
            $custom_tables = array();
            
            foreach ($tables as $table) {
                $table_name = $table[0];
                // Filter out WordPress core tables and only include custom tables
                if (strpos($table_name, $wpdb->prefix) === 0 && 
                    !in_array($table_name, array(
                        $wpdb->prefix . 'posts',
                        $wpdb->prefix . 'users',
                        $wpdb->prefix . 'comments',
                        $wpdb->prefix . 'options',
                        $wpdb->prefix . 'postmeta',
                        $wpdb->prefix . 'usermeta',
                        $wpdb->prefix . 'terms',
                        $wpdb->prefix . 'term_taxonomy',
                        $wpdb->prefix . 'term_relationships',
                        $wpdb->prefix . 'termmeta',
                        $wpdb->prefix . 'commentmeta',
                        $wpdb->prefix . 'links',
                        $wpdb->prefix . 'blog_versions',
                        $wpdb->prefix . 'blogs',
                        $wpdb->prefix . 'blog_versions',
                        $wpdb->prefix . 'registration_log',
                        $wpdb->prefix . 'signups',
                        $wpdb->prefix . 'site',
                        $wpdb->prefix . 'sitemeta',
                        $wpdb->prefix . 'users'
                    ))) {
                    $custom_tables[] = $table_name;
                }
            }
            
            return $custom_tables;
        }
    }
}
