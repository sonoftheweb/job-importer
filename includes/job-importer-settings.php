<?php
// Create settings page
function job_importer_settings_page() {
    add_options_page(
        'Job Importer Settings',
        'Job Importer',
        'manage_options',
        'job-importer',
        'job_importer_settings_page_content'
    );
}

// Settings page content
function job_importer_settings_page_content() {
    ?>
    <div class="wrap">
        <h1>Job Importer Settings</h1>
        <form method="post" action="options.php">
            <?php
            settings_fields( 'job_importer_settings_group' );
            do_settings_sections( 'job-importer' );
            submit_button();
            ?>
        </form>
        <h2>Manual Import</h2>
        <p>
            <a href="<?php echo esc_url( add_query_arg( 'job_importer_run', 'true' ) ); ?>" class="button button-primary">Run Job Import</a>
        </p>
    </div>
    <?php
}

// Register settings
add_action( 'admin_init', 'job_importer_register_settings' );
function job_importer_register_settings() {
    register_setting( 'job_importer_settings_group', 'job_importer_feed_url' );
}