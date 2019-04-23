<?php
/**
* Ajax handler class
*
* @since 1.0.0
*/
class WCRW_Admin_Ajax {

    /**
     * Load autometically when class initiate
     *
     * @since 1.0.0
     */
    public function __construct() {
        add_action( 'wp_ajax_add_request_note', [ $this, 'add_request_note' ], 10, 1 );
        add_action( 'wp_ajax_delete_request_note', [ $this, 'delete_request_note' ], 10, 1 );
    }

    /**
     * Add request notes
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function add_request_note() {
        $postdata = wp_unslash( $_POST );

        if ( ! wp_verify_nonce( $postdata['nonce'], 'wcrw_admin_nonce' ) ) {
            wp_send_json_error( __( 'Nonce verification faild', 'wc-return-warranty-management' ) );
        }

        wp_parse_str( $postdata['formData'], $data );

        if ( empty( $data['request_id'] ) ) {
            wp_send_json_error( __( 'Request not found', 'wc-return-warranty-management' ) );
        }

        $response = wcrw_add_request_note( $data );

        if ( is_wp_error( $response ) ) {
            wp_send_json_error( $response->get_error_message() );
        }

        wp_send_json_success( $response );
    }

    /**
     * Delete request note
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function delete_request_note() {
        $postdata = wp_unslash( $_POST );

        if ( ! wp_verify_nonce( $postdata['nonce'], 'wcrw_admin_nonce' ) ) {
            wp_send_json_error( __( 'Nonce verification faild', 'wc-return-warranty-management' ) );
        }

        if ( empty( $postdata['id'] ) ) {
            wp_send_json_error( __( 'Request id not found', 'wc-return-warranty-management' ) );
        }

        $response = wcrw_delete_request_note( (int)$postdata['id'] );

        if ( is_wp_error( $response ) ) {
            wp_send_json_error( $response->get_error_message() );
        }

        wp_send_json_success( __( 'Note deleted successfully', 'wc-return-warranty-management' ) );
    }
}
