<?php
/**
 * Create return request for admin.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

$text_align = is_rtl() ? 'right' : 'left';

/**
 * @hooked WC_Emails::email_header() Output the email header
 */
do_action( 'woocommerce_email_header', $email_heading, $email ); ?>

    <p><?php printf( __( 'Your return request ID #%d is now <strong>%s</strong>', 'wc-return-warranty' ), $request['id'], wcrw_warranty_request_status( $request['status'] ) ); ?></p>

    <div style="margin-bottom: 40px;">
        <table class="td" cellspacing="0" cellpadding="6" style="width: 100%; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif;" border="1">
            <thead>
                <tr>
                    <th class="td" scope="col" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php esc_html_e( 'Product', 'wc-return-warranty' ); ?></th>
                    <th class="td" scope="col" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php esc_html_e( 'Quantity', 'wc-return-warranty' ); ?></th>
                    <th class="td" scope="col" style="text-align:<?php echo esc_attr( $text_align ); ?>;"><?php esc_html_e( 'Price', 'wc-return-warranty' ); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php
                    if ( ! empty( $request['items'] ) ) {
                        foreach ( $request['items'] as $key => $item ) {
                            ?>
                                <tr class="<?php echo esc_attr( apply_filters( 'woocommerce_order_item_class', 'order_item', $item, $order ) ); ?>">
                                    <td class="td" style="text-align:<?php echo esc_attr( $text_align ); ?>; vertical-align: middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; word-wrap:break-word;">
                                        <?php
                                            echo '<img src="' . $item['thumbnail'] . '" width="32px" height="32px">';
                                            echo $item['title'];
                                        ?>
                                    </td>
                                    <td class="td" style="text-align:<?php echo esc_attr( $text_align ); ?>; vertical-align: middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; word-wrap:break-word;">
                                        <?php echo wp_kses_post( apply_filters( 'woocommerce_email_order_item_quantity', $item['quantity'], $item ) ); ?>
                                    </td>
                                    <td class="td" style="text-align:<?php echo esc_attr( $text_align ); ?>; vertical-align: middle; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; word-wrap:break-word;">
                                        <?php echo wp_kses_post( wc_price( $item['price'] ) ); ?>
                                    </td>
                                </tr>
                            <?php
                        }
                    }
                ?>
            </tbody>
        </table>
    </div>

<?php
/**
 * @hooked WC_Emails::customer_details() Shows customer details
 * @hooked WC_Emails::email_address() Shows email address
 */
do_action( 'woocommerce_email_customer_details', $order, $sent_to_admin, $plain_text, $email );

/**
 * @hooked WC_Emails::email_footer() Output the email footer
 */
do_action( 'woocommerce_email_footer', $email );
