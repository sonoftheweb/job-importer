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

// Add button to run import manually
add_action( 'admin_notices', 'job_importer_manual_import_button' );
function job_importer_manual_import_button() {
    ?>
    <div class="notice notice-info">
        <p>
            <a href="<?php echo esc_url( add_query_arg( 'job_importer_run', 'true' ) ); ?>" class="button button-primary">Run Job Import</a>
        </p>
    </div>
    <?php
}

// Run manual import if button is clicked
if ( isset( $_GET['job_importer_run'] ) && $_GET['job_importer_run'] === 'true' ) {
    job_importer_import_jobs();

    // Display a success message
    add_action( 'admin_notices', 'job_importer_success_message' );
}

function job_importer_success_message() {
    ?>
    <div class="notice notice-success is-dismissible">
        <p>Jobs imported successfully!</p>
    </div>
    <?php
}