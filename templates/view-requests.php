<div class="wcrw-view-request-wrapper">
    <?php
    try {
        $request_id = get_query_var( 'view-warranty-request' );
        $request    = wcrw_get_warranty_request( [ 'id' => $request_id ] );

        if ( empty( $request ) ) {
            throw new Exception( __( 'No warranty request found', 'wc-return-warranty-management' ) );
        }
        ?>
            <?php do_action( 'wcrw_view_request_start', $request, $request_id ); ?>

            <p><?php echo sprintf( __( 'Request ID is #<mark>%s</mark> for Order #<mark class="order-number">%s</mark> was placed on <mark class="order-date">%s</mark> is currently <mark class="order-status">%s</mark>', 'wc-return-warranty-management' ), $request['id'], $request['order_id'], date_i18n( get_option( 'date_format' ), strtotime( $request['created_at'] ) ), wcrw_warranty_request_status( $request['status'] ) ); ?></p>
            <section class="woocommerce-order-details">
                <h2 class="woocommerce-order-details__title"><?php _e( 'Other details', 'wc-return-warranty-management' ); ?></h2>

                <table class="woocommerce-orders-table woocommerce-MyAccount-orders shop_table shop_table_responsive my_account_orders" style="margin-top: 10px;">
                    <tbody>
                        <tr>
                            <td><strong><?php _e( 'Type :', 'wc-return-warranty-management' ) ?></strong></td>
                            <td><?php echo wcrw_warranty_request_type( $request['type'] ); ?></td>
                        </tr>
                        <tr>
                            <td><strong><?php _e( 'Products :', 'wc-return-warranty-management' ) ?></strong></td>
                            <td>
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
                            <td><strong><?php _e( 'Status :', 'wc-return-warranty-management' ) ?></strong></td>
                            <td><?php echo wcrw_warranty_request_status( $request['status'] ); ?></td>
                        </tr>
                    </tbody>
                </table>
            </section>

            <?php do_action( 'wcrw_view_request_after_main_deatils', $request, $request_id ); ?>

            <section class="woocommerce-order-details">
                <h2 class="woocommerce-order-details__title"><?php _e( 'Other details', 'wc-return-warranty-management' ); ?></h2>

                <table class="woocommerce-orders-table woocommerce-MyAccount-orders shop_table shop_table_responsive my_account_orders" style="margin-top: 10px;">
                    <tbody>
                        <tr>
                            <td><?php _e( 'Reasons', 'wc-return-warranty-management' ); ?></td>
                            <td><?php echo $request['reasons']; ?></td>
                        </tr>

                        <?php if ( ! empty( $request['meta'] ) ): ?>
                            <?php foreach ( $request['meta'] as $key => $value ) : ?>
                                <tr>
                                    <td><?php echo $key; ?></td>
                                    <td><?php echo $value; ?></td>
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