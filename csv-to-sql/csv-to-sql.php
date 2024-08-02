<?php
/*
Plugin Name: CSV to SQL
Description: A custom plugin to import CSV data into a custom database table and perform computations.
Version: 1.0
Author: Premium Vortex
Author URI: https://premiumvortex.com/
*/

// Ensure the script is not accessed directly
if ( ! defined( 'ABSPATH' ) ) {
    exit; // Exit if accessed directly.
}

define( 'CSV_TO_SQL_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );

// Include necessary files
require_once CSV_TO_SQL_PLUGIN_DIR . 'includes/class-csv-to-sql.php';

// Initialize the plugin
function csv_to_sql_init() {
    new CSV_To_SQL();
}

add_action( 'plugins_loaded', 'csv_to_sql_init' );
