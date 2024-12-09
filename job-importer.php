<?php
/**
 * Plugin Name: Job Importer
 * Description: Imports jobs from a JSON feed into WordPress posts.
 * Version: 0.0.1-alpha
 * Author: Jacob Ekanem
 */

// Include necessary files
require_once( plugin_dir_path( __FILE__ ) . 'includes/job-importer-settings.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/job-importer-functions.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/job-importer-menu.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/job-importer-page.php' );

// Register settings page
add_action( 'admin_menu', 'job_importer_settings_page' );

// Register activation and deactivation hooks
register_activation_hook( __FILE__, 'job_importer_activate' );
register_deactivation_hook( __FILE__, 'job_importer_deactivate' );

// Activation function (schedules daily import and creates table)
function job_importer_activate() {
    global $wpdb;

    // Schedule daily import
    if ( ! wp_next_scheduled( 'job_importer_daily_import' ) ) {
        wp_schedule_event( time(), 'daily', 'job_importer_daily_import' );
    }

    // Create the database table
    $table_name = $wpdb->prefix . 'job_importer_jobs';
    $charset_collate = $wpdb->get_charset_collate();

    $sql = "CREATE TABLE IF NOT EXISTS $table_name (
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        job_id varchar(255) NOT NULL,
        job_title text NOT NULL,
        job_link text NOT NULL,
        job_city varchar(255) DEFAULT NULL,
        job_location varchar(255) DEFAULT NULL,
        job_added_date datetime DEFAULT NULL,
        lang_code varchar(10) DEFAULT NULL,
        PRIMARY KEY  (id),
        UNIQUE KEY job_id (job_id)
    ) $charset_collate;";

    require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
    dbDelta( $sql );
    error_log( print_r( $result, true ) );
}

// Deactivation function (removes scheduled import)
function job_importer_deactivate() {
    wp_clear_scheduled_hook( 'job_importer_daily_import' );
}

// Hook for daily import
add_action( 'job_importer_daily_import', 'job_importer_import_jobs' );