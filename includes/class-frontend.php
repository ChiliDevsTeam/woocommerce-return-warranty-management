<?php
if ( ! defined( 'ABSPATH' ) ) exit;

/**
* Frontend cart, checkout and product releated class
*
* @since 1.0.0
*/
class WCRW_Frontend {

    /**
     * Load autometically when class initiate
     *
     * @since 1.0.0
     */
    public function __construct() {
        add_action( 'woocommerce_before_add_to_cart_button', [ $this, 'show_warranty' ] );
        add_filter( 'woocommerce_add_cart_item_data', [ $this, 'add_cart_item_data' ], 10, 2 );
        add_filter( 'woocommerce_add_cart_item', [ $this, 'add_cart_item' ], 10, 1 );
        add_filter( 'woocommerce_add_to_cart_validation', [ $this, 'add_cart_validation' ], 10, 2 );
        add_filter( 'woocommerce_get_cart_item_from_session', [ $this, 'get_cart_item_from_session' ], 10, 2 );
        add_filter( 'woocommerce_get_item_data', [ $this, 'get_item_data' ], 10, 2 );
        add_action( 'woocommerce_add_to_cart', [ $this, 'add_warranty_index' ], 10, 6 );
        add_action( 'woocommerce_checkout_create_order_line_item', [ $this, 'order_item_meta' ], 10, 3 );
        add_action( 'woocommerce_order_status_changed', [ $this, 'order_status_changed' ], 10, 4 );
        add_action( 'woocommerce_order_item_meta_end', [ $this, 'show_warranty_details' ], 11, 4 );
        add_shortcode( 'warranty-requests', [ $this, 'render_warranty_request' ] );

    }

    /**
     * Show warranty in product page
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function show_warranty() {
        global $product;

        if ( ! $product->is_type( 'simple' ) ) {
            return;
        }

        $product_id     = $product->get_id();
        $warranty       = wcrw_get_warranty_settings( $product_id );
        $warranty_label = $warranty['label'];

        if ( $warranty['type'] == 'included_warranty' ) {
            if ( 'no' == $warranty['hide_warranty'] ) {
                if ( $warranty['length'] == 'limited' ) {
                    $value      = $warranty['length_value'];
                    $duration   = wcrw_get_duration_value( $warranty['length_duration'], $value );
                    echo '<p class="wcrw_warranty_info"><strong>'. $warranty_label .':</strong> '. $value .' '. $duration .'</p>';
                } else {
                    echo '<p class="wcrw_warranty_info"><strong>'. $warranty_label .':</strong> '. __( 'Lifetime', 'wc-return-warranty' ) .'</p>';
                }
            }
        } elseif ( $warranty['type'] == 'addon_warranty' )                    {
            $addons = $warranty['addon_settings'];

            if ( is_array($addons) && !empty($addons) ) {
                echo '<p class="wcrw_warranty_info"><strong>'. $warranty_label .': </strong> <select name="wcrw_warranty">';
                echo '<option value="-1">'. __( 'No warranty', 'wc-return-warranty' ) .'</option>';

                foreach ( $addons as $x => $addon ) {
                    $price    = $addon['price'];
                    $value    = $addon['length'];
                    $duration = wcrw_get_duration_value( $addon['duration'], $value );

                    if ( $value == 0 && $price == 0 ) {
                        echo '<option value="-1">'. __( 'No warranty', 'wc-return-warranty' ) .'</option>';
                    } else {
                        if ( $price == 0 ) {
                            $price = __( 'Free', 'wc-return-warranty' );
                        } else {
                            $price = wc_price( $price );
                        }
                        echo '<option value="'. $x .'">'. $value .' '. $duration . ' &mdash; '. $price .'</option>';
                    }
                }

                echo '</select></p>';
            }
        } else {
            echo '<p class="wcrw_warranty_info"></p>';
        }
    }

    /**
     * Add warranty item data in cart item data
     *
     * @param array $item_data
     * @param integer $product_id
     *
     * @return array
     */
    public function add_cart_item_data( $item_data, $product_id ) {
        if ( isset( $_POST['wcrw_warranty']) && $_POST['wcrw_warranty'] !== '' ) {
            $item_data['wcrw_warranty_index'] = $_POST['wcrw_warranty'];
        }

        return $item_data;
    }

    /**
     * Add cart item and update price depneding on add ons
     *
     * @param array $item_data
     *
     * @return array
     */
    public function add_cart_item( $item_data ) {
        $_product       = $item_data['data'];
        $warranty_index = false;

        if ( isset( $item_data['wcrw_warranty_index'] ) ) {
            $warranty_index = $item_data['wcrw_warranty_index'];
        }

        $product_id = $_product->get_id();
        $warranty   = wcrw_get_warranty_settings( $product_id );

        if ( $warranty ) {
            if ( $warranty['type'] == 'addon_warranty' && $warranty_index !== false ) {
                $addons                           = $warranty['addon_settings'];
                $item_data['wcrw_warranty_index'] = $warranty_index;
                $add_cost                         = 0;

                if ( isset( $addons[$warranty_index] ) && !empty( $addons[$warranty_index] ) ) {
                    $addon = $addons[$warranty_index];
                    if ( $addon['price'] > 0 ) {
                        $add_cost += $addon['price'];
                        $_product->set_price( $_product->get_price() + $add_cost );
                    }
                }
            }
        }

        return $item_data;
    }

    /**
     * Add cart validaion wheater customer need to force choose any warranty or not
     *
     * @param string $valid
     * @param integer $product_id
     *
     * @return boolean
     */
    public function add_cart_validation( $valid = '', $product_id = 0 ) {
        $product_id = ! empty( $_REQUEST['variation_id'] ) ? absint( $_REQUEST['variation_id'] ) : $product_id;

        $warranty       = wcrw_get_warranty_settings( $product_id );
        $warranty_label = $warranty['label'];

        if ( $warranty['type'] == 'addon_warranty' && ! isset( $_REQUEST['wcrw_warranty'] ) ) {
            $error = sprintf( __( 'Please select your %s first.', 'wc-return-warranty' ), $warranty_label );
            wc_add_notice( $error, 'error' );
            return false;
        }

        return $valid;
    }

        /**
     * Get warranty index and add it to the cart item
     *
     * @since 1.0.0
     *
     * @param array $cart_item
     * @param array $values
     *
     * @return array $cart_item
     */
    function get_cart_item_from_session( $cart_item, $values ) {

        if ( isset( $values['wcrw_warranty_index'] ) ) {
            $cart_item['wcrw_warranty_index'] = $values['wcrw_warranty_index'];
            $cart_item = $this->add_cart_item( $cart_item );
        }

        return $cart_item;
    }

    /**
     * Returns warranty data about a cart item
     *
     * @since 1.0.0
     *
     * @param array $other_data
     * @param array $cart_item
     *
     * @return array $other_data
     */
    function get_item_data( $other_data, $cart_item ) {
        $_product   = $cart_item['data'];
        $product_id = $_product->get_id();

        $warranty       = wcrw_get_warranty_settings( $product_id );
        $warranty_label = $warranty['label'];

        if ( $warranty ) {
            if ( $warranty['type'] == 'addon_warranty' && isset( $cart_item['wcrw_warranty_index'] ) ) {
                $addons         = $warranty['addon_settings'];
                $warranty_index = $cart_item['wcrw_warranty_index'];

                if ( isset( $addons[$warranty_index] ) && ! empty( $addons[$warranty_index] ) ) {
                    $addon         = $addons[$warranty_index];
                    $name          = $warranty_label;
                    $duration_unit = wcrw_get_duration_value( $addon['duration'], $addon['length'] );
                    $value         = $addon['length'] . ' ' . $duration_unit;

                    if ( $addon['price'] > 0 ) {
                        $value .= ' (' . wc_price( $addon['price'] ) . ')';
                    }

                    $other_data[] = array(
                        'name'      => $name,
                        'value'     => $value,
                        'display'   => ''
                    );
                }
            } elseif ( $warranty['type'] == 'included_warranty' ) {
                if ( 'no' == $warranty['hide_warranty'] ) {
                    if ( $warranty['length'] == 'lifetime' ) {
                        $other_data[] = array(
                            'name'      => $warranty_label,
                            'value'     => __( 'Lifetime', 'wc-return-warranty' ),
                            'display'   => ''
                        );
                    } elseif ( $warranty['length'] == 'limited' ) {
                        $duration_unit = wcrw_get_duration_value( $warranty['length_duration'], $warranty['length_value'] );
                        $string = $warranty['length_value'] . ' ' . $duration_unit;
                        $other_data[] = array(
                            'name'      => $warranty_label,
                            'value'     => $string,
                            'display'   => ''
                        );
                    }
                }
            }
        }

        return $other_data;
    }

    /**
     * Add warranty index in cart item session
     *
     * @param string $cart_key
     * @param int $product_Id
     * @param int $quantity
     * @param int $variation_id
     * @param object $variation
     * @param array $cart_item_data
     *
     * @return void
     */
    function add_warranty_index( $cart_key, $product_id, $quantity, $variation_id = null, $variation = null, $cart_item_data = null ) {
        if ( isset( $_POST['wcrw_warranty'] ) && $_POST['wcrw_warranty'] !== '' ) {
            WC()->cart->cart_contents[$cart_key]['wcrw_warranty_index'] = $_POST['wcrw_warranty'];
        }
    }

    /**
     * Include add-ons line item meta.
     *
     * @param  WC_Order_Item_Product $item          Order item data.
     * @param  string                $cart_item_key Cart item key.
     * @param  array                 $values        Order item values.
     *
     * @return  void
     */
    public function order_item_meta( $item, $cart_item_key, $values ) {
        $_product       = $values['data'];
        $_product_id    = $_product->get_id();
        $warranty       = wcrw_get_warranty_settings( $_product_id );
        $warranty_label = $warranty['label'];

        if ( $warranty ) {
            $item_id = $item->save();

            if ( $warranty['type'] == 'addon_warranty' ) {
                $warranty_index = isset( $values['wcrw_warranty_index'] ) ? $values['wcrw_warranty_index'] : false;
                wc_add_order_item_meta( $item_id, '_wcrw_item_warranty_selected', $warranty_index );
            }

            if ( 'no_warranty' !== $warranty['type'] ) {
                wc_add_order_item_meta( $item_id, '_wcrw_item_warranty', $warranty );
            }
        }
    }

        /**
     * Handle order status changes and update warranty date as order
     * completed date
     *
     * @param integer $order_id
     * @param string $old_status
     * @param string $new_status
     * @param object $order
     *
     * @return void
     */
    public function order_status_changed( $order_id, $old_status, $new_status, $order ) {
        $order = wc_get_order( $order_id );

        if ( 'completed' !== $new_status ) {
            return;
        }

        $items          = $order->get_items();
        $has_warranty   = false;

        foreach ( $items as $item ) {
            $warranty       = false;
            $addon_index    = false;
            $metas          = ( isset( $item['item_meta'] ) ) ? $item['item_meta'] : [];

            foreach ( $metas as $key => $value ) {
                if ( $key == '_wcrw_item_warranty' ) {
                    $warranty = maybe_unserialize( $value );
                }
            }

            if ( $warranty ) {
                $order->set_date_completed( current_time( 'mysql' ) );
                $order->save();
                break;
            }
        }
    }

    /**
     * Show warranty details with order item meta
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function show_warranty_details( $item_id, $item, $order, $plain_text = false ) {
        if ( $item['type'] != 'line_item' ) {
            return;
        }

        $warranty = wc_get_order_item_meta( $item_id, '_wcrw_item_warranty', true );

        if ( $warranty && ! empty( $order->get_id() ) ) {
            $name = $value = $expiry = false;
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
                <p>
                    <strong><?php echo wp_kses_post( $name ); ?></strong>:
                    <span>
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
                    </span>
                </p>
            </div>
            <?php
        }
    }

    /**
     * Show Warranty Requests via shortcodes
     *
     * @since 1.1.3
     *
     * @return void
     */
    public function render_warranty_request( $atts ) {
        $frontend_settings = get_option( 'wcrw_frontend' );

        $attributes = shortcode_atts( [
            'user_id'  => get_current_user_id(),
            'order_by' => 'id',
            'order'    => 'desc',
            'per_page' => ! empty( $frontend_settings['requests_per_page'] ) ? $frontend_settings['requests_per_page'] : 20
        ], $atts );

        $attributes['is_shortcode'] = true;

        $data              = [];
        $pagination_html   = '';
        $total_count       = wcrw_get_warranty_request( [ 'count' => true, 'customer_id' => $attributes['user_id'] ] );
        $page              = isset( $_GET['cpage'] ) ? abs( (int) $_GET['cpage'] ) : 1;
        $offset            = ( $page * $attributes['per_page'] ) - $attributes['per_page'];
        $total_page        = ceil( $total_count['total_count']/$attributes['per_page'] );

        if ( ! empty( $_GET['status'] ) ) {
            $data['status'] = $_GET['status'];
        }

        $data['number']      = $attributes['per_page'];
        $data['offset']      = $offset;
        $data['customer_id'] = $attributes['user_id'];

        if( $total_page > 1 ){
            $pagination_html = '<div class="pagination-wrap">';
            $page_links = paginate_links( array(
                'base'      => add_query_arg( 'cpage', '%#%' ),
                'format'    => '',
                'type'      => 'array',
                'prev_text' => __( '&laquo; Previous', 'wc-return-warranty' ),
                'next_text' => __( 'Next &raquo;', 'wc-return-warranty' ),
                'total'     => $total_page,
                'current'   => $page
            ) );
            $pagination_html .= '<ul class="pagination"><li>';
            $pagination_html .= join( "</li>\n\t<li>", $page_links );
            $pagination_html .= "</li>\n</ul>\n";
            $pagination_html .= '</div>';
        };

        $requests = wcrw_get_warranty_request( $data );

        do_action( 'wcrw_load_before_shortcode_render', $requests, $attributes, $data );

        ob_start();
        if ( ! empty( $_GET['request_id'] ) ) {
            $id = $_GET[ 'request_id' ];
            require_once WCRW_TEMPLATE_PATH . '/view-requests.php';
        } else {
            require_once WCRW_TEMPLATE_PATH . '/all-requests.php';
        }
        return ob_get_clean();
    }

}
