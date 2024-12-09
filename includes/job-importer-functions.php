<?php

// Function to import jobs
function job_importer_import_jobs() {
    // Get the feed URL from settings
    $feed_url = get_option( 'job_importer_feed_url', 'https://www.lifemark.ca/api/json/adp-jobs' );

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

        // Check if the job already exists
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
    // Get all existing job IDs
    $args = array(
        'post_type' => 'job',
        'fields' => 'ids',
        'posts_per_page' => -1,
    );
    $existing_job_ids = get_posts( $args );

    // Find IDs to delete
    $ids_to_delete = array_diff( $existing_job_ids, $current_job_ids );

    // Delete old jobs
    foreach ( $ids_to_delete as $id ) {
        wp_delete_post( $id, true );
    }
}