<?php

if ( ! class_exists( 'TSV_Processor' ) ) {

    class TSV_Processor {

        public function handle_file_upload() {
            if ( ! isset( $_POST['tsv_upload_nonce'] ) || ! wp_verify_nonce( $_POST['tsv_upload_nonce'], 'tsv_upload' ) ) {
                $this->log_error('Nonce verification failed');
                wp_die( 'Nonce verification failed', 'Error', array( 'response' => 403 ) );
            }

            if ( ! isset( $_FILES['tsv_file'] ) || $_FILES['tsv_file']['error'] != UPLOAD_ERR_OK ) {
                $this->log_error('No file uploaded or there was an upload error');
                wp_die( 'No file uploaded or there was an upload error', 'Error', array( 'response' => 400 ) );
            }

            $file = $_FILES['tsv_file']['tmp_name'];
            $filename = sanitize_file_name( $_FILES['tsv_file']['name'] );
            $upload_dir = CSV_TO_SQL_PLUGIN_DIR . 'assets/uploaded_TSV/';

            if ( ! file_exists( $upload_dir ) ) {
                mkdir( $upload_dir, 0755, true );
            }

            $destination = $upload_dir . $filename;

            if ( move_uploaded_file( $_FILES['tsv_file']['tmp_name'], $destination ) ) {
                $this->process_tsv_file( $destination, $filename );
            } else {
                $this->log_error('Error uploading file');
                wp_die( 'Error uploading file', 'Error', array( 'response' => 500 ) );
            }

            wp_redirect( admin_url( 'admin.php?page=csv-to-sql' ) );
            exit;
        }

        public function process_tsv_file( $file, $filename ) {
            global $wpdb;

            $table_name = $wpdb->prefix . sanitize_title( pathinfo( $filename, PATHINFO_FILENAME ) );
            $database_name = $wpdb->dbname;

            if ( ( $handle = fopen( $file, 'r' ) ) !== FALSE ) {
                $header = fgetcsv( $handle, 0, "\t" );
                $sanitized_header = array_map(array($this, 'sanitize_column_name'), $header);

                // Create the table with an auto-increment primary key
                $create_table_query = "CREATE TABLE `$table_name` (
                    `id` INT NOT NULL AUTO_INCREMENT PRIMARY KEY, ";
                foreach ( $sanitized_header as $column ) {
                    $create_table_query .= "`$column` TEXT, ";
                }
                $create_table_query = rtrim( $create_table_query, ', ' ) . ');';

                if ( $wpdb->query( $create_table_query ) === FALSE ) {
                    $this->log_error("Failed to create table $table_name: " . $wpdb->last_error);
                    wp_die( 'Failed to create table', 'Error', array( 'response' => 500 ) );
                }

                $row_count = 0;
                $column_count = count($sanitized_header);

                while ( ( $row = fgetcsv( $handle, 0, "\t" ) ) !== FALSE ) {
                    $row_data = array_combine( $sanitized_header, $row );
                    if ( $wpdb->insert( $table_name, $row_data ) === FALSE ) {
                        $this->log_error("Failed to insert row into $table_name: " . $wpdb->last_error);
                        wp_die( 'Failed to insert row', 'Error', array( 'response' => 500 ) );
                    }
                    $row_count++;
                }

                fclose( $handle );

                if ( $this->table_exists( $table_name ) ) {
                    $this->log_info("Table $table_name created and populated from $filename in database $database_name");
                    $this->log_info("Number of rows read: $row_count, Number of columns: $column_count");
                } else {
                    $this->log_error("Table $table_name does not exist after creation attempt");
                    wp_die( 'Table creation failed', 'Error', array( 'response' => 500 ) );
                }
            }

            // Log table name to keep track of created tables
            $this->log_table_name( $table_name );
        }

        private function sanitize_column_name($column_name) {
            return sanitize_title_with_dashes($column_name);
        }

        private function table_exists( $table_name ) {
            global $wpdb;
            return $wpdb->get_var("SHOW TABLES LIKE '$table_name'") == $table_name;
        }

        private function log_table_name( $table_name ) {
            $log_file = CSV_TO_SQL_PLUGIN_DIR . 'assets/uploaded_TSV/tables.log';
            file_put_contents( $log_file, $table_name . PHP_EOL, FILE_APPEND );
        }

        public function get_created_tables() {
            $log_file = CSV_TO_SQL_PLUGIN_DIR . 'assets/uploaded_TSV/tables.log';
            if ( file_exists( $log_file ) ) {
                $tables = file( $log_file, FILE_IGNORE_NEW_LINES );
                return $tables;
            }
            return array();
        }

        public function handle_table_deletion() {
            if ( ! isset( $_POST['delete_table_nonce'] ) || ! wp_verify_nonce( $_POST['delete_table_nonce'], 'delete_table' ) ) {
                $this->log_error('Nonce verification failed for table deletion');
                wp_die( 'Nonce verification failed', 'Error', array( 'response' => 403 ) );
            }

            if ( isset( $_POST['table_name'] ) ) {
                $table_name = sanitize_text_field( $_POST['table_name'] );
                global $wpdb;
                if ( $wpdb->query( "DROP TABLE IF EXISTS `$table_name`" ) === FALSE ) {
                    $this->log_error("Failed to delete table $table_name: " . $wpdb->last_error);
                    wp_die( 'Failed to delete table', 'Error', array( 'response' => 500 ) );
                }

                // Remove table name from log
                $this->remove_table_name_from_log( $table_name );

                $this->log_info("Table $table_name deleted");
            }

            wp_redirect( admin_url( 'admin.php?page=csv-to-sql' ) );
            exit;
        }

        private function remove_table_name_from_log( $table_name ) {
            $log_file = CSV_TO_SQL_PLUGIN_DIR . 'assets/uploaded_TSV/tables.log';
            if ( file_exists( $log_file ) ) {
                $tables = file( $log_file, FILE_IGNORE_NEW_LINES );
                $tables = array_diff( $tables, array( $table_name ) );
                file_put_contents( $log_file, implode( PHP_EOL, $tables ) . PHP_EOL );
            }
        }

        private function log_info( $message ) {
            require_once CSV_TO_SQL_PLUGIN_DIR . 'includes/class-csv-logger.php';
            $logger = new CSV_Logger();
            $logger->log( $message );
        }

        private function log_error( $message ) {
            require_once CSV_TO_SQL_PLUGIN_DIR . 'includes/class-csv-logger.php';
            $logger = new CSV_Logger();
            $logger->log( "ERROR: $message" );
        }
    }
}
