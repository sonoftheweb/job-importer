<?php

class Job_Importer_Page {

    public static function display_page_content() {
        ?>
        <div class="wrap">
            <h1>Job Importer</h1>

            <?php 
            if ( isset( $_GET['job_importer_run'] ) && $_GET['job_importer_run'] === 'true' ) {
                $job_importer_functions = new Job_Importer_Functions();
                $job_importer_functions->import_jobs();

                echo '<div class="notice notice-success is-dismissible"><p>Jobs imported successfully!</p></div>';
            }
            ?>

            <h2>Manual Import</h2>
            <p>Click the button below to manually import jobs from the feed.</p>
            <p>
                <a href="<?php echo esc_url( add_query_arg( 'job_importer_run', 'true' ) ); ?>" class="button button-primary">Run Job Import</a>
            </p>
        </div>
        <?php
    }
}