<?php

class Job_Importer_Functions {

    public function __construct() {
        add_action( 'job_importer_daily_import', array( $this, 'import_jobs' ) );
    }

    public function activate() {
        global $wpdb;

        if ( ! wp_next_scheduled( 'job_importer_daily_import' ) ) {
            wp_schedule_event( time(), 'daily', 'job_importer_daily_import' );
        }

        $table_name = $wpdb->prefix . 'job_importer_jobs';
        $charset_collate = $wpdb->get_charset_collate();

        $sql = "CREATE TABLE IF NOT EXISTS $table_name (
            id mediumint(9) NOT NULL AUTO_INCREMENT,
            job_id varchar(255) NOT NULL,
            job_title text NOT NULL,
            job_link text NOT NULL,
            job_city varchar(255) DEFAULT NULL,
            job_location varchar(255) DEFAULT NULL,
            job_added_date datetime DEFAULT NULL,
            lang_code varchar(10) DEFAULT NULL,
            job_code varchar(255) DEFAULT NULL, 
            category varchar(255) DEFAULT NULL, 
            job_branding varchar(255) DEFAULT NULL, 
            nid varchar(255) DEFAULT NULL UNIQUE, 
            PRIMARY KEY  (id)
        ) $charset_collate;";

        require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
        dbDelta( $sql );
    }

    public function deactivate() {
        wp_clear_scheduled_hook( 'job_importer_daily_import' );
    }

    public function import_jobs() {
        global $wpdb;
        $table_name = $wpdb->prefix . 'job_importer_jobs';
        $feed_url = get_option( 'job_importer_feed_url' );

        if ( empty( $feed_url ) ) {
            error_log( 'Job Importer Error: Feed URL is not set.' );
            return;
        }

        $response = wp_remote_get( $feed_url, array( 'timeout' => 60 ) );

        if ( is_wp_error( $response ) ) {
            error_log( 'Job Importer Error: Failed to fetch feed - ' . $response->get_error_message() );
            return;
        }

        $data = json_decode( wp_remote_retrieve_body( $response ), true );

        if ( json_last_error() !== JSON_ERROR_NONE ) {
            error_log( 'Job Importer Error: Invalid JSON data - ' . json_last_error_msg() );
            return;
        }

        if ( ! isset( $data['nodes'] ) || ! is_array( $data['nodes'] ) ) {
            error_log( 'Job Importer Error: Missing or invalid "nodes" data.' );
            return;
        }

        $current_job_ids = array();

        foreach ( $data['nodes'] as $node ) {
            if ( ! isset( $node['itemId'], $node['langCode'], $node['title'], $node['link'] ) ) {
                error_log( 'Job Importer Error: Missing required job data: ' . print_r( $node, true ) );
                continue;
            }

            if ( $node['langCode'] !== 'en_CA' ) {
                error_log( 'Job Importer Notice: Skipping non-English job: ' . print_r( $node, true ) );
                continue;
            }

            $current_job_ids[] = $node['itemId'];

            $job_data = array(
                'job_id'          => $node['itemId'],
                'job_title'       => $node['jobTitle'],
                'job_link'        => $node['link'],
                'job_city'        => isset( $node['jobCity'] ) ? $node['jobCity'] : null,
                'job_location'    => isset( $node['jobLocation'] ) ? $node['jobLocation'] : null,
                'job_added_date'  => isset( $node['jobAddedDate'] ) ? date( 'Y-m-d H:i:s', strtotime( $node['jobAddedDate'] ) ) : null,
                'lang_code'       => $node['langCode'],
                'job_code'        => isset( $node['jobCode'] ) ? $node['jobCode'] : null,
                'category'        => isset( $node['category'] ) ? $node['category'] : null,
                'job_branding'    => isset( $node['jobBranding'] ) ? $node['jobBranding'] : null,
                'nid'             => isset( $node['nid'] ) ? $node['nid'] : null,
            );

            $existing_job = $wpdb->get_row( $wpdb->prepare(
                "SELECT * FROM {$table_name} WHERE nid = %s",
                $node['nid']
            ));

            if ($existing_job) {
                $wpdb->update(
                    $table_name,
                    $job_data,
                    array('nid' => $node['nid'])
                );
            } else {
                $wpdb->insert($table_name, $job_data);
            }
        }

        $this->delete_old_jobs( $current_job_ids );
    }

    private function delete_old_jobs( $current_job_ids ) {
        global $wpdb;
        $table_name = $wpdb->prefix . 'job_importer_jobs';
        $existing_job_ids = $wpdb->get_col( "SELECT job_id FROM $table_name" );
        $ids_to_delete = array_diff( $existing_job_ids, $current_job_ids );

        foreach ( $ids_to_delete as $job_id ) {
            $wpdb->delete( $table_name, array( 'job_id' => $job_id ) );
        }
    }
}