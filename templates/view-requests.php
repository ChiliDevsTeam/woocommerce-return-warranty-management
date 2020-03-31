<div class="wcrw-view-request-wrapper <?php echo ( ! empty( $attributes['is_shortcode'] ) && $attributes['is_shortcode'] ) ? 'wcrw-shortcode-single-requests' : ''; ?>">
    <?php
    try {
        $request_id  = ( ! empty( $attributes['is_shortcode'] ) && $attributes['is_shortcode'] ) ? $id : get_query_var( 'view-warranty-request' );
        $request     = wcrw_get_warranty_request( [ 'id' => $request_id ] );
        $form_fields = wcrw_get_form_fields_data();

        if ( empty( $request ) ) {
            throw new Exception( __( 'No warranty request found', 'wc-return-warranty' ) );
        }
        ?>
            <?php do_action( 'wcrw_view_request_start', $request, $request_id ); ?>

            <p><?php echo sprintf( __( 'Request ID is #<mark>%s</mark> for Order #<mark class="order-number">%s</mark> was placed on <mark class="order-date">%s</mark> is currently <mark class="order-status">%s</mark>', 'wc-return-warranty' ), $request['id'], $request['order_id'], date_i18n( get_option( 'date_format' ), strtotime( $request['created_at'] ) ), wcrw_warranty_request_status( $request['status'] ) ); ?></p>
            <section class="woocommerce-order-details">
                <table class="woocommerce-orders-table woocommerce-MyAccount-orders shop_table shop_table_responsive my_account_orders" style="margin-top: 10px;">
                    <tbody>
                        <tr>
                            <td class="label"><strong><?php _e( 'Type :', 'wc-return-warranty' ) ?></strong></td>
                            <td class="value"><?php echo wcrw_warranty_request_type( $request['type'] ); ?></td>
                        </tr>
                        <tr>
                            <td class="label"><strong><?php _e( 'Products :', 'wc-return-warranty' ) ?></strong></td>
                            <td class="value">
                                <?php
                                    $product_list = [];
                                    foreach ( $request['items'] as $item ) {
                                        $product_list[] = '<a href="' . $item['url'] . '">' . $item['title'] . '</a> <strong>x ' . $item['quantity'] . '</strong>';
                                    }

                                    echo implode( ', ', $product_list );
                                ?>
                            </td>
                        </tr>
                        <tr>
                            <td class="label"><strong><?php _e( 'Status :', 'wc-return-warranty' ) ?></strong></td>
                            <td class="value"><?php echo wcrw_warranty_request_status( $request['status'] ); ?></td>
                        </tr>
                    </tbody>
                </table>
            </section>

            <?php do_action( 'wcrw_view_request_after_main_deatils', $request, $request_id ); ?>

            <section class="woocommerce-order-details">
                <h2 class="woocommerce-order-details__title"><?php _e( 'Other details', 'wc-return-warranty' ); ?></h2>

                <table class="woocommerce-orders-table woocommerce-MyAccount-orders shop_table shop_table_responsive my_account_orders" style="margin-top: 10px;">
                    <tbody>
                        <?php if ( ! empty( $request['reasons'] ) ): ?>
                            <tr>
                                <td class="label"><?php _e( 'Reasons', 'wc-return-warranty' ); ?></td>
                                <td class="value"><?php echo $request['reasons']; ?></td>
                            </tr>
                        <?php endif ?>

                        <?php if ( ! empty( $request['meta'] ) ): ?>
                            <?php foreach ( $request['meta'] as $meta ) : ?>
                                <tr class="<?php echo $meta['key']; ?>">
                                    <td class="label"><?php echo apply_filters( 'wcrw_render_request_meta_label', $meta['label'], $meta, $form_fields ); ?></td>
                                    <td class="value"><?php echo apply_filters( 'wcrw_render_request_meta_value', $meta['value'], $meta, $form_fields ); ?></td>
                                </tr>
                            <?php endforeach ?>
                        <?php endif ?>
                    </tbody>
                </table>
            </section>

            <?php do_action( 'wcrw_view_request_end', $request, $request_id ); ?>
        <?php
    } catch (Exception $e) {
        wc_print_notice( $e->getMessage(), 'error' );
    }
    ?>
</div>
