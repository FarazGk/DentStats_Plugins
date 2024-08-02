<?php

if ( ! class_exists( 'CSV_To_SQL_Logs' ) ) {

    class CSV_To_SQL_Logs {

        public function add_logs_menu() {
            add_submenu_page(
                'csv-to-sql',
                'Logs',
                'Logs',
                'manage_options',
                'csv-to-sql-logs',
                array( $this, 'create_logs_page' )
            );
        }

        public function create_logs_page() {
            ?>
            <div class="wrap">
                <h1>Logs</h1>
                <div style="background: #fff; border: 1px solid #ccc; padding: 10px; max-height: 600px; overflow-y: scroll;">
                    <pre><?php echo esc_html( $this->get_logs() ); ?></pre>
                </div>
            </div>
            <?php
        }

        public function get_logs() {
            $log_file = CSV_TO_SQL_PLUGIN_DIR . 'logs/csv-to-sql.log';
            if ( file_exists( $log_file ) ) {
                return file_get_contents( $log_file );
            }
            return 'No logs available.';
        }
    }
}
