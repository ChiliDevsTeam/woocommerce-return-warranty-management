<?php
if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

if ( ! class_exists( 'WC_Email' ) ) {
    return;
}

/**
 * Class WCRW_Create_Request_Admin
 */
class WCRW_Update_Request extends WC_Email {
    /**
     * Create an instance of the class.
     *
     * @access public
     *
     * @return void
     */
    function __construct() {
        // Email slug we can use to filter other data.
        $this->id             = 'wcrw_update_request_notification';
        $this->title          = __( 'Request Status', 'wc-return-warranty' );
        $this->customer_email = true;
        $this->description    = __( 'An email sent to the customer when a admin update request status', 'wc-return-warranty' );

        // Template paths.
        $this->template_html  = 'emails/wcrw-update-request-status.php';
        $this->template_plain = 'emails/plain/wcrw-update-request-status.php';
        $this->template_base  = WCRW_PATH . '/templates/';
        $this->placeholders   = [
            '{site_title}'    => $this->get_blogname(),
            '{site_url}'      => '',
            '{customer_name}' => '',
            '{new_status}'    => '',
            '{old_status}'    => '',
        ];

        // Action to which we hook onto to send the email.
        add_action( 'wcrw_update_request_status', [ $this, 'trigger' ], 15, 3 );

        parent::__construct();
    }

    /**
     * Get email subject.
     *
     * @since  1.0.0
     *
     * @return string
     */
    public function get_default_subject() {
        return __( '[{site_name}] Request Updated', 'wc-return-warranty' );
    }

    /**
     * Get email heading.
     *
     * @since  1.0.0
     *
     * @return string
     */
    public function get_default_heading() {
        return __( 'Your request updated to {new_status}', 'wc-return-warranty' );
    }

    /**
     * Trigger Function that will send this email to the customer.
     *
     * @access public
     * @return void
     */
    function trigger( $request_id, $old_status, $new_status ) {
        $this->object = wcrw_get_warranty_request( [ 'id' => $request_id ] );

        $this->recipient  = $this->object['customer']['email'];

        $this->placeholders['{customer_name}'] = $this->object['customer']['first_name'] . ' ' . $this->object['customer']['last_name'];
        $this->placeholders['{site_name}']     = $this->get_from_name();
        $this->placeholders['{site_url}']      = site_url();
        $this->placeholders['{new_status}']    = wcrw_warranty_request_status( $new_status );
        $this->placeholders['{old_status}']    = wcrw_warranty_request_status( $old_status );

        if ( $this->is_enabled() && $this->get_recipient() ) {
            $this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
        }
    }

    /**
     * Get content html.
     *
     * @access public
     * @return string
     */
    public function get_content_html() {
        return wc_get_template_html( $this->template_html, array(
            'request'       => $this->object,
            'email_heading' => $this->get_heading(),
            'order'         => wc_get_order( $this->object['order_id'] ),
            'sent_to_admin' => false,
            'plain_text'    => false,
            'email'         => $this
        ), '', $this->template_base );
    }
    /**
     * Get content plain.
     *
     * @return string
     */
    public function get_content_plain() {
        return wc_get_template_html( $this->template_plain, array(
            'request'       => $this->object,
            'order'         => wc_get_order( $this->object['order_id'] ),
            'email_heading' => $this->get_heading(),
            'sent_to_admin' => false,
            'plain_text'    => true,
            'email'         => $this
        ), '', $this->template_base );
    }
}

