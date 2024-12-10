<?php

class Job_Importer_Settings {

    public function __construct() {
        add_action( 'admin_menu', array( $this, 'add_settings_page' ) );
        add_action( 'admin_init', array( $this, 'register_settings' ) );
    }

    public function add_settings_page() {
        add_options_page(
            'Job Importer Settings',
            'Job Importer',
            'manage_options',
            'job-importer-settings',
            array( $this, 'display_settings_page_content' )
        );
    }

    public function display_settings_page_content() {
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

    public function register_settings() {
        register_setting( 'job_importer_settings_group', 'job_importer_feed_url' );
    }
}