<div class="wcrw-new-request-wrapper">
    <?php
        try {
            $order_id = get_query_var( 'new-warranty-request' );
            $order    = wc_get_order( $order_id );

            if ( ! $order ) {
                throw new Exception( __( 'Invalid order', 'wc-return-warranty' ) );
            }
            ?>
            <form method="post" enctype="multipart/form-data">
                <table class="woocommerce-orders-table woocommerce-MyAccount-orders shop_table shop_table_responsive my_account_orders">
                    <thead>
                        <tr>
                            <th></th>
                            <th><?php _e( 'Product Name', 'wc-return-warranty' ) ?></th>
                            <th><?php _e( 'Expiry Date', 'wc-return-warranty' ) ?></th>
                            <th><?php _e( 'Quantity', 'wc-return-warranty' ) ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ( $order->get_items() as $key => $item ): ?>
                            <?php
                                $product = $item->get_product();
                                $warranty_item = new WCRW_Warranty_Item( $item->get_id() );

                                if ( ! $warranty_item->has_warranty() ) {
                                    continue;
                                }
                            ?>
                            <tr class="woocommerce-orders-table__row">
                                <td>
                                    <input type="checkbox" name="products[product_id][]" value="<?php echo $product->get_id(); ?>">
                                    <input type="hidden" name="products[item_id][]" value="<?php echo $item->get_id(); ?>">
                                </td>
                                <td><?php echo $product->get_name(); ?></td>
                                <td><?php echo $warranty_item->get_expiry_date_string(); ?></td>
                                <td>
                                    <select name="products[quantity][]" id="quantity">
                                        <?php for ( $i=1; $i <= $warranty_item->get_remaining_quantity(); $i++ ) : ?>
                                            <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                        <?php endfor; ?>
                                    </select>
                                </td>
                            </tr>
                        <?php endforeach ?>
                    </tbody>
                </table>

                <div class="wcrw-request-form">
                    <?php foreach ( wcrw_get_warranty_request_form_fields() as $field ): ?>
                        <?php if ( 'heading' == $field['type'] ): ?>
                            <?php echo wcrw_render_request_form_field( $field ); ?>
                        <?php else: ?>
                            <p class="form-row form-row-wide" id="<?php echo $field['id'] ?>">
                                <?php echo wcrw_render_request_form_field( $field ); ?>
                            </p>
                        <?php endif ?>
                    <?php endforeach ?>
                    <p class="form-row form-row-wide form-submit-wrapper" id="wcrw-request-form-submit">
                        <input type="hidden" name="order_id" value="<?php echo $order->get_id(); ?>">
                        <?php wp_nonce_field( 'wcrw_request_form_nonce_action', 'wcrw_request_form_nonce' ); ?>
                        <input type="submit" class="button" name="save_warranty_request" value="<?php _e( 'Send Request', 'wc-return-warranty' ); ?>">
                    </p>
                </div>
            </form>
            <?php
        } catch ( Exception $e ) {
            wc_print_notice( $e->getMessage(), 'error' );
        }
    ?>
</div>
<script>
    jQuery(document).ready( function() {
        jQuery('.wcrw-select2').select2();
    });
</script>
