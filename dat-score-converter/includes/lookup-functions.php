<?php
// Exit if accessed directly
if (!defined('ABSPATH')) {
    exit;
}

if (!class_exists('DAT_Score_Lookup_Functions')) {

    class DAT_Score_Lookup_Functions {

        /**
         * Get the configured table name from options
         */
        public static function get_configured_table() {
            $table_name = get_option('dat_score_converter_table', '');
            error_log('DAT Score Converter - Retrieved table name from options: ' . $table_name);
            return $table_name;
        }

        /**
         * Get the configured lookup column from options
         */
        public static function get_configured_lookup_column() {
            return get_option('dat_score_converter_lookup_column', '');
        }

        /**
         * Set the configuration options
         */
        public static function set_configuration($table_name, $lookup_column) {
            update_option('dat_score_converter_table', sanitize_text_field($table_name));
            update_option('dat_score_converter_lookup_column', sanitize_text_field($lookup_column));
        }

        /**
         * Perform a score lookup
         */
        public static function lookup_score($lookup_value, $table_name = null, $lookup_column = null) {
            global $wpdb;

            // Use configured values if not provided
            if (!$table_name) {
                $table_name = self::get_configured_table();
            }
            if (!$lookup_column) {
                $lookup_column = self::get_configured_lookup_column();
            }

            if (empty($table_name) || empty($lookup_column)) {
                return new WP_Error('no_config', 'No table or lookup column configured.');
            }

            // Validate inputs
            if (!preg_match('/^[a-zA-Z0-9_\-\.]+$/', $table_name)) {
                return new WP_Error('invalid_table', 'Invalid table name: ' . $table_name);
            }

            if (!preg_match('/^[a-zA-Z0-9_]+$/', $lookup_column)) {
                return new WP_Error('invalid_column', 'Invalid column name: ' . $lookup_column);
            }

            // Check if table exists
            $table_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name));
            if (!$table_exists) {
                return new WP_Error('table_not_found', 'Table does not exist.');
            }

            // Perform the lookup
            $query = $wpdb->prepare("SELECT * FROM `" . esc_sql($table_name) . "` WHERE `" . esc_sql($lookup_column) . "` = %s", $lookup_value);
            $data = $wpdb->get_row($query, ARRAY_A);

            if ($data) {
                // Sanitize output data
                array_walk_recursive($data, function (&$value) {
                    $value = sanitize_text_field($value);
                });
                return $data;
            }

            return new WP_Error('no_data', 'No data found for the specified lookup value.');
        }

        /**
         * Get all available tables
         */
        public static function get_available_tables() {
            global $wpdb;
            
            $tables = $wpdb->get_results("SHOW TABLES", ARRAY_N);
            $custom_tables = array();
            
            foreach ($tables as $table) {
                $table_name = $table[0];
                // Filter out WordPress core tables
                if (strpos($table_name, $wpdb->prefix) === 0 && 
                    !in_array($table_name, self::get_wp_core_tables())) {
                    $custom_tables[] = $table_name;
                }
            }
            
            return $custom_tables;
        }

        /**
         * Get table columns
         */
        public static function get_table_columns($table_name) {
            global $wpdb;

            // Debug logging
            error_log('DAT Score Converter - Getting columns for table: ' . $table_name);

            if (!preg_match('/^[a-zA-Z0-9_\-\.]+$/', $table_name)) {
                error_log('DAT Score Converter - Table name failed regex validation: ' . $table_name);
                return new WP_Error('invalid_table', 'Invalid table name: ' . $table_name);
            }

            $table_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name));
            if (!$table_exists) {
                return new WP_Error('table_not_found', 'Table does not exist.');
            }

            $columns = $wpdb->get_col("SHOW COLUMNS FROM `" . esc_sql($table_name) . "`");
            return $columns;
        }

        /**
         * Get sample data from table
         */
        public static function get_sample_data($table_name, $limit = 5) {
            global $wpdb;

            if (!preg_match('/^[a-zA-Z0-9_\-\.]+$/', $table_name)) {
                return new WP_Error('invalid_table', 'Invalid table name: ' . $table_name);
            }

            $table_exists = $wpdb->get_var($wpdb->prepare("SHOW TABLES LIKE %s", $table_name));
            if (!$table_exists) {
                return new WP_Error('table_not_found', 'Table does not exist.');
            }

            $query = $wpdb->prepare("SELECT * FROM `" . esc_sql($table_name) . "` LIMIT %d", intval($limit));
            $data = $wpdb->get_results($query, ARRAY_A);

            if ($data) {
                // Sanitize output data
                array_walk_recursive($data, function (&$value) {
                    $value = sanitize_text_field($value);
                });
                return $data;
            }

            return array();
        }

        /**
         * Format score data for display
         */
        public static function format_score_display($data, $format = 'table') {
            if (is_wp_error($data)) {
                return '<p class="error">Error: ' . $data->get_error_message() . '</p>';
            }

            if (empty($data)) {
                return '<p>No data found.</p>';
            }

            switch ($format) {
                case 'table':
                    return self::format_as_table($data);
                case 'list':
                    return self::format_as_list($data);
                case 'json':
                    return json_encode($data, JSON_PRETTY_PRINT);
                default:
                    return self::format_as_table($data);
            }
        }

        /**
         * Format data as HTML table
         */
        private static function format_as_table($data) {
            if (empty($data)) {
                return '<p>No data to display.</p>';
            }

            // Columns to hide (case-insensitive)
            $hidden_columns = array('id', 'dat');
            
            // Columns to show (in order) - case-insensitive matching
            $display_columns = array('aa', 'sns', 'bio', 'gch', 'och', 'pat', 'qrt', 'rct');
            
            // Helper function to find case-insensitive key
            $find_key = function($data, $search_key) {
                $search_key_lower = strtolower($search_key);
                foreach (array_keys($data) as $key) {
                    if (strtolower($key) === $search_key_lower) {
                        return $key;
                    }
                }
                return null;
            };

            $html = '<div class="dat-score-table-wrapper">';
            $html .= '<table class="dat-score-table">';
            $html .= '<thead><tr>';
            
            // Header row - only show allowed columns
            foreach ($display_columns as $col) {
                $actual_key = $find_key($data, $col);
                if ($actual_key) {
                    $html .= '<th>' . esc_html(strtoupper($col)) . '</th>';
                }
            }
            $html .= '</tr></thead>';
            
            // Data row
            $html .= '<tbody><tr>';
            foreach ($display_columns as $col) {
                $actual_key = $find_key($data, $col);
                if ($actual_key) {
                    $html .= '<td>' . esc_html($data[$actual_key]) . '</td>';
                }
            }
            $html .= '</tr></tbody>';
            
            $html .= '</table>';
            $html .= '</div>';
            return $html;
        }

        /**
         * Format data as HTML list
         */
        private static function format_as_list($data) {
            if (empty($data)) {
                return '<p>No data to display.</p>';
            }

            // Columns to hide (case-insensitive)
            $hidden_columns = array('id', 'dat');
            
            // Columns to show (in order) - case-insensitive matching
            $display_columns = array('aa', 'sns', 'bio', 'gch', 'och', 'pat', 'qrt', 'rct');
            
            // Helper function to find case-insensitive key
            $find_key = function($data, $search_key) {
                $search_key_lower = strtolower($search_key);
                foreach (array_keys($data) as $key) {
                    if (strtolower($key) === $search_key_lower) {
                        return $key;
                    }
                }
                return null;
            };

            $html = '<ul class="dat-score-list">';
            foreach ($display_columns as $col) {
                $actual_key = $find_key($data, $col);
                if ($actual_key) {
                    $html .= '<li><strong>' . esc_html(strtoupper($col)) . ':</strong> ' . esc_html($data[$actual_key]) . '</li>';
                }
            }
            $html .= '</ul>';
            return $html;
        }

        /**
         * Get WordPress core table names
         */
        private static function get_wp_core_tables() {
            global $wpdb;
            
            return array(
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
                $wpdb->prefix . 'registration_log',
                $wpdb->prefix . 'signups',
                $wpdb->prefix . 'site',
                $wpdb->prefix . 'sitemeta'
            );
        }

        /**
         * Validate configuration
         */
        public static function validate_configuration() {
            $table_name = self::get_configured_table();
            $lookup_column = self::get_configured_lookup_column();

            // Debug logging
            error_log('DAT Score Converter - Validating config. Table: ' . $table_name . ', Column: ' . $lookup_column);

            if (empty($table_name) || empty($lookup_column)) {
                return new WP_Error('incomplete_config', 'Configuration is incomplete. Please select a table and lookup column in the admin panel.');
            }

            $columns = self::get_table_columns($table_name);
            if (is_wp_error($columns)) {
                return $columns;
            }

            if (!in_array($lookup_column, $columns)) {
                return new WP_Error('invalid_column', 'Configured lookup column does not exist in the selected table.');
            }

            return true;
        }
    }
}
