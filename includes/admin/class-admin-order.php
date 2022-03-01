<?php

/**
* Admin order related functions
*/
class WCRW_Admin_Order {

    /**
     * Load autometically when class initiate
     *
     * @since 1.0.0
     */
    public function __construct() {
        add_action( 'woocommerce_before_order_itemmeta', [ $this, 'render_item_warranty' ], 10, 3 );
        add_filter( 'woocommerce_hidden_order_itemmeta', [ $this, 'hidden_warranty_meta' ], 10, 1 );
        add_action( 'delete_post', [ $this, 'admin_on_delete_order' ] );
    }

    /**
     * Display an order item's warranty data
     *
     * @param int           $item_id
     * @param array         $item
     * @param WC_Product    $product
     */
    public function render_item_warranty( $item_id, $item, $product ) {
        global $post, $wp;

        if ( $item['type'] != 'line_item' ) {
            return;
        }

        $warranty = wc_get_order_item_meta( $item_id, '_wcrw_item_warranty', true );

        if ( $post ) {
            $order_id = $post->ID;
        } elseif ( isset( $_POST['order_id'] ) ) {
            $order_id = $_POST['order_id'];
        }

        if ( $warranty && ! empty( $order_id ) ) {
            $name = $value = $expiry = false;

            $order = wc_get_order( $order_id );
            $order_date = $order->get_date_completed() ? $order->get_date_completed()->date( 'Y-m-d H:i:s' ) : false;

            if ( empty( $warranty['label'] ) ) {
                $product_warranty = wcrw_get_warranty_settings( $item['product_id'] );
                $warranty['label'] = $product_warranty['label'];
            }
            if ( $warranty['type'] == 'addon_warranty' ) {
                $addons         = $warranty['addon_settings'];
                $warranty_index = wc_get_order_item_meta( $item_id, '_wcrw_item_warranty_selected', true );

                if ( $warranty_index !== false && isset( $addons[$warranty_index] ) && !empty( $addons[$warranty_index] ) ) {
                    $addon  = $addons[$warranty_index];
                    $name   = $warranty['label'];
                    $unit  = wcrw_get_duration_value( $addon['duration'], $addon['length'] );
                    $value = $addon['length'] . ' ' . $unit;

                    if ( $order_date ) {
                        $expiry = wcrw_get_warranty_date( $order_date, $addon['length'], $addon['duration'] );
                    }

                }
            } elseif ( $warranty['type'] == 'included_warranty' ) {
                if ( $warranty['length'] == 'limited' ) {
                    $name   = $warranty['label'];
                    $unit  = wcrw_get_duration_value( $warranty['length_duration'], $warranty['length_value'] );
                    $value = $warranty['length_value'] . ' ' . $unit;

                    if ( $order_date ) {
                        $expiry = wcrw_get_warranty_date( $order_date, $warranty['length_value'], $warranty['length_duration'] );
                    }
                }
            }

            if ( ! $name || ! $value ) {
                return;
            }

            ?>
            <div class="view">
                <table cellspacing="0" class="display_meta">
                    <tr>
                        <th style="width: 39%;"><?php echo wp_kses_post( $name ); ?>:</th>
                        <td>
                        <?php
                            echo wp_kses_post( $value );

                            if ( $expiry ) {
                                if ( current_time('timestamp') > strtotime( $expiry ) ) {
                                    echo ' <small>(' . __( 'expired on', 'wc-return-warranty' ) . ' ' . $expiry .')</small>';
                                } else {
                                    echo ' <small>(' . __( 'expires', 'wc-return-warranty' ) . ' ' . $expiry .')</small>';
                                }
                            }
                        ?>
                        </td>
                    </tr>
                </table>
            </div>
            <?php
        }
    }

    /**
     * Hide warranty metas
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function hidden_warranty_meta( $hidden_meta ) {
        $hidden_meta[] = '_wcrw_item_warranty_selected';
        return $hidden_meta;
    }

    /**
     * On delete order delete the return requests
     *
     * @since 1.1.6
     *
     * @return void
     */
    public function admin_on_delete_order( $post_id ) {
        global $wpdb;

        $post = get_post( $post_id );

        if ( 'shop_order' == $post->post_type ) {
            $request_ids = $wpdb->get_results( $wpdb->prepare( "SELECT `id` from `{$wpdb->prefix}wcrw_warranty_requests` WHERE `order_id`='%d'", intval( $post_id ) ) );
            if ( ! empty( $request_ids) ) {
                foreach ( $request_ids as $request ) {
                    wcrw_delete_warranty_request( $request->id );
                }
            }
        }
    }

}
