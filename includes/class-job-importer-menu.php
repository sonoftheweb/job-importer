<?php

class Job_Importer_Menu {

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_menu_page' ) );
    }

    public function add_menu_page() {
        add_menu_page(
            'Job Importer',
            'Job Importer',
            'manage_options',
            'job-importer-manual',
            array( 'Job_Importer_Page', 'display_page_content' ),
            'dashicons-database-import',
            20
        );
    }
}