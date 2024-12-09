<?php
// Menu page content with the import button
function job_importer_page_content() {
    ?>
    <div class="wrap">
        <h1>Job Importer</h1>

        <?php 
        // Run manual import if button is clicked
        if ( isset( $_GET['job_importer_run'] ) && $_GET['job_importer_run'] === 'true' ) {
            job_importer_import_jobs();

            // Display a success message
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