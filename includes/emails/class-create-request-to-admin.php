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
class WCRW_Create_Request_Admin extends WC_Email {
    /**
     * Create an instance of the class.
     *
     * @access public
     *
     * @return void
     */
    function __construct() {
        $this->id          = 'wcrw_create_request_to_admin';
        $this->title       = __( 'New Request to Admin', 'wc-return-warranty' );
        $this->description = __( 'An email sent to the admin when a customer send a return request to admin', 'wc-return-warranty' );

        // Template paths.
        $this->template_html  = 'emails/wcrw-create-request-admin.php';
        $this->template_plain = 'emails/plain/wcrw-create-request-admin.php';
        $this->template_base  = WCRW_PATH . '/templates/';
        $this->recipient = $this->get_option( 'recipient', get_option( 'admin_email' ) );
        $this->placeholders   = [
            '{site_title}'    => $this->get_blogname(),
            '{site_url}'      => '',
            '{customer_name}' => '',
        ];

        // Action to which we hook onto to send the email.
        add_action( 'wcrw_created_warranty_request', [ $this, 'trigger' ], 20, 3 );

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
        return __( '[{site_name}] A New Return Request is submitted from ({customer_name})', 'wc-return-warranty' );
    }

    /**
     * Get email heading.
     *
     * @since  1.0.0
     *
     * @return string
     */
    public function get_default_heading() {
        return __( 'New request created by {customer_name}', 'wc-return-warranty' );
    }

    /**
     * Trigger Function that will send this email to the customer.
     *
     * @access public
     * @return void
     */
    function trigger( $request_id, $args, $postdata ) {
        $this->object = wcrw_get_warranty_request( [ 'id' => $request_id ] );

        $this->placeholders['{customer_name}'] = $this->object['customer']['first_name'] . ' ' . $this->object['customer']['last_name'];
        $this->placeholders['{site_name}']     = $this->get_from_name();
        $this->placeholders['{site_url}']      = site_url();

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
            'sent_to_admin' => true,
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
            'sent_to_admin' => true,
            'plain_text'    => true,
            'email'         => $this
        ), '', $this->template_base );
    }

}

