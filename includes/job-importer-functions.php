<?php

// Function to import jobs
function job_importer_import_jobs() {
    global $wpdb;

    // Get the feed URL from the saved setting
    $feed_url = get_option( 'job_importer_feed_url' );

    // If the feed URL is not set, don't proceed
    if ( empty( $feed_url ) ) {
        error_log( 'Job Importer Error: Feed URL is not set.' );
        return;
    }

    // Fetch the JSON feed
    $response = wp_remote_get( $feed_url );

    // Error validation: Check if the request was successful
    if ( is_wp_error( $response ) ) {
        error_log( 'Job Importer Error: Failed to fetch feed - ' . $response->get_error_message() );
        return;
    }

    // Parse the JSON data
    $data = json_decode( wp_remote_retrieve_body( $response ), true );

    // Error validation: Check if JSON data is valid
    if ( json_last_error() !== JSON_ERROR_NONE ) {
        error_log( 'Job Importer Error: Invalid JSON data - ' . json_last_error_msg() );
        return;
    }

    // Error validation: Check if 'nodes' exists in the data
    if ( ! isset( $data['nodes'] ) || ! is_array( $data['nodes'] ) ) {
        error_log( 'Job Importer Error: Missing or invalid "nodes" data.' );
        return;
    }

    // Array to store the IDs of the current jobs in the feed
    $current_job_ids = array();

    // Loop through the job nodes
    foreach ( $data['nodes'] as $node ) {

        // Error validation: Check for required fields
        if ( ! isset( $node['itemId'], $node['langCode'], $node['title'], $node['link'] ) ) {
            error_log( 'Job Importer Error: Missing required job data: ' . print_r( $node, true ) );
            continue; // Skip to the next node
        }

        // Only import English jobs
        if ( $node['langCode'] !== 'en_CA' ) {
            continue;
        }

        // Add the job ID to the array of current job IDs
        $current_job_ids[] = $node['itemId'];

        // Prepare data for the database
        $job_data = array(
            'job_id'          => $node['itemId'],
            'job_title'       => $node['title'],
            'job_link'        => $node['link'],
            'job_city'        => isset( $node['jobCity'] ) ? $node['jobCity'] : null,
            'job_location'    => isset( $node['jobLocation'] ) ? $node['jobLocation'] : null,
            'job_added_date'  => isset( $node['jobAddedDate'] ) ? date( 'Y-m-d H:i:s', strtotime( $node['jobAddedDate'] ) ) : null,
            'lang_code'       => $node['langCode'],
        );

        // Insert or update the job in the database
        $table_name = $wpdb->prefix . 'job_importer_jobs';
        $wpdb->replace( $table_name, $job_data );

        // Check if the job already exists in WordPress
        $existing_post = get_page_by_title( $node['itemId'], OBJECT, 'job' );

        // Prepare post data
        $post_data = array(
            'post_title'   => $node['title'],
            'post_content' => '', // You can add content here if needed
            'post_status'  => 'publish',
            'post_type'    => 'job',
            'meta_input'   => array(
                'job_id'  => $node['itemId'],
                'job_link' => $node['link'],
                // Add other meta data as needed
            ),
        );

        // Update existing job or create a new one
        if ( $existing_post ) {
            $post_data['ID'] = $existing_post->ID;
            wp_update_post( $post_data );
        } else {
            wp_insert_post( $post_data );
        }
    }

    // Delete jobs that are no longer in the feed
    job_importer_delete_old_jobs( $current_job_ids );
}

// Function to delete jobs not in the feed
function job_importer_delete_old_jobs( $current_job_ids ) {
    global $wpdb; // Access the WordPress database object

    // Get all existing job IDs from the database
    $table_name = $wpdb->prefix . 'job_importer_jobs';
    $existing_job_ids = $wpdb->get_col( "SELECT job_id FROM $table_name" );

    // Find IDs to delete
    $ids_to_delete = array_diff( $existing_job_ids, $current_job_ids );

    // Delete old jobs from the database and WordPress
    foreach ( $ids_to_delete as $job_id ) {
        // Delete from the database
        $wpdb->delete( $table_name, array( 'job_id' => $job_id ) );

        // Delete from WordPress posts (if they exist)
        $existing_post = get_page_by_title( $job_id, OBJECT, 'job' );
        if ( $existing_post ) {
            wp_delete_post( $existing_post->ID, true );
        }
    }
}