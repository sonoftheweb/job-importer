<?php
/**
 * Plugin Name: Job Importer
 * Description: Imports jobs from a JSON feed into WordPress posts.
 * Version: 0.0.1-alpha
 * Author: Jacob Ekanem
 */

// Include necessary files
require_once( plugin_dir_path( __FILE__ ) . 'includes/class-job-importer-settings.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/class-job-importer-functions.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/class-job-importer-menu.php' );
require_once( plugin_dir_path( __FILE__ ) . 'includes/class-job-importer-page.php' );

// Initialize classes
$job_importer_settings = new Job_Importer_Settings();
$job_importer_functions = new Job_Importer_Functions();
$job_importer_menu = new Job_Importer_Menu();
$job_importer_page = new Job_Importer_Page();

// Register activation and deactivation hooks
register_activation_hook( __FILE__, array( $job_importer_functions, 'activate' ) );
register_deactivation_hook( __FILE__, array( $job_importer_functions, 'deactivate' ) );