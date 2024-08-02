<?php

if ( ! class_exists( 'CSV_Logger' ) ) {

    class CSV_Logger {

        public function log( $message ) {
            $log_dir = plugin_dir_path( __FILE__ ) . '../logs';
            if ( ! file_exists( $log_dir ) ) {
                mkdir( $log_dir, 0755, true );
            }
            $log_file = $log_dir . '/csv-to-sql.log';
            $timestamp = date( "Y-m-d H:i:s" );
            $log_message = "[{$timestamp}] {$message}\n";
            $log_message .= "----------------------------------------------------------------\n";
            file_put_contents( $log_file, $log_message, FILE_APPEND );
        }
    }
}
