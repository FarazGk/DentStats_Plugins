<?php

if ( ! class_exists( 'CSV_To_SQL' ) ) {

    class CSV_To_SQL {

        public function __construct() {
            add_action( 'admin_menu', array( $this, 'add_admin_menu' ) );
            add_action( 'admin_post_upload_tsv', array( $this, 'handle_file_upload' ) );
            add_action( 'admin_post_delete_table', array( $this, 'handle_table_deletion' ) );
        }

        public function add_admin_menu() {
            require_once CSV_TO_SQL_PLUGIN_DIR . 'admin/class-csv-to-sql-admin.php';
            require_once CSV_TO_SQL_PLUGIN_DIR . 'admin/class-csv-to-sql-logs.php';
            $admin = new CSV_To_SQL_Admin();
            $admin->add_admin_menu();
            $logs = new CSV_To_SQL_Logs();
            $logs->add_logs_menu();
        }

        public function handle_file_upload() {
            require_once CSV_TO_SQL_PLUGIN_DIR . 'includes/class-tsv-processor.php';
            $processor = new TSV_Processor();
            $processor->handle_file_upload();
        }

        public function handle_table_deletion() {
            require_once CSV_TO_SQL_PLUGIN_DIR . 'includes/class-tsv-processor.php';
            $processor = new TSV_Processor();
            $processor->handle_table_deletion();
        }
    }
}
