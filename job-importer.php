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

// Activation function (schedules daily import)
function job_importer_activate() {
    if ( ! wp_next_scheduled( 'job_importer_daily_import' ) ) {
        wp_schedule_event( time(), 'daily', 'job_importer_daily_import' );
    }
}

// Deactivation function (removes scheduled import)
function job_importer_deactivate() {
    wp_clear_scheduled_hook( 'job_importer_daily_import' );
}

// Hook for daily import
add_action( 'job_importer_daily_import', 'job_importer_import_jobs' );