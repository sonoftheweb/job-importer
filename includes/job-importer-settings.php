<?php
// Create settings page
function job_importer_settings_page() {
    add_options_page(
        'Job Importer Settings',
        'Job Importer',
        'manage_options',
        'job-importer-settings', // Changed slug to avoid conflict
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
            ?>
            <table class="form-table">
                <tr valign="top">
                    <th scope="row"><label for="job_importer_feed_url">Feed URL</label></th>
                    <td><input type="text" id="job_importer_feed_url" name="job_importer_feed_url" value="<?php echo esc_attr( get_option( 'job_importer_feed_url' ) ); ?>" class="regular-text" /></td>
                </tr>
            </table>
            <?php
            submit_button();
            ?>
        </form>
    </div>
    <?php
}

// Register settings
add_action( 'admin_init', 'job_importer_register_settings' );
function job_importer_register_settings() {
    register_setting( 'job_importer_settings_group', 'job_importer_feed_url' );
    // Add sanitization if necessary
}