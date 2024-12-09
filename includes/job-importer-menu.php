<?php
// Add menu page for manual import
add_action( 'admin_menu', 'job_importer_menu_page' );
function job_importer_menu_page() {
    add_menu_page(
        'Job Importer',
        'Job Importer',
        'manage_options',
        'job-importer-manual',
        'job_importer_page_content',
        'dashicons-database-import',
        20
    );
}