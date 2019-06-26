<?php
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * Merge user defined arguments into defaults array.
 *
 * This function is similiar to wordpress wp_parse_args().
 * It's support multidimensional array.
 *
 * @param  array $args
 * @param  array $defaults Optional.
 *
 * @return array
 */
function wcrw_parse_args( &$args, $defaults = [] ) {
    $args     = (array) $args;
    $defaults = (array) $defaults;
    $r        = $defaults;

    foreach ( $args as $k => &$v ) {
        if ( is_array( $v ) && isset( $r[ $k ] ) ) {
            $r[ $k ] = wcrw_parse_args( $v, $r[ $k ] );
        } else {
            $r[ $k ] = $v;
        }
    }

    return $r;
}

/**
 * Warranty request status
 *
 * @since 1.0.0
 *
 * @param string $status
 *
 * @return array|String
 */
function wcrw_warranty_request_status( $status = '' ) {
    $statuses = apply_filters( 'wcrw_warranty_request_status', [
        'new'        => __( 'New', 'wc-return-warranty-management' ),
        'processing' => __( 'Processing', 'wc-return-warranty-management' ),
        'completed'  => __( 'Completed', 'wc-return-warranty-management' ),
        'rejected'   => __( 'Rejected', 'wc-return-warranty-management' ),
        'reviewing'  => __( 'Reviewing', 'wc-return-warranty-management' ),
    ] );

    if ( ! empty( $status ) ) {
        return ! empty( $statuses[$status] ) ? $statuses[$status] : '';
    }

    return $statuses;
}

/**
 * Warranty request status
 *
 * @since 1.0.0
 *
 * @param string $status
 *
 * @return array|String
 */
function wcrw_warranty_request_status_html( $status = '' ) {
    $status_label = wcrw_warranty_request_status( $status );

    if ( is_array( $status_label ) ) {
        return false;
    }

    return '<span class="status-class status-' . $status . '">' . $status_label . '<span>';
}

/**
 * Return and Warranty Types
 *
 * @since 1.0.0
 *
 * @return array|string
 */
function wcrw_warranty_types( $type = '' ) {
    $warranty_type = apply_filters( 'wcrw_warranty_types', [
        'no_warranty'       => __( 'No Warranty', 'wc-return-warranty-management' ),
        'included_warranty' => __( 'Included Warranty', 'wc-return-warranty-management' ),
        'addon_warranty'    => __( 'Price base Warranty', 'wc-return-warranty-management' )
    ] );

    if ( ! empty( $type ) ) {
        return isset( $warranty_type[$type] ) ? $warranty_type[$type] : '';
    }

    return $warranty_type;
}

/**
 * Warranty Length if included warranty
 *
 * @since 1.0.0
 *
 * @return string | Array
 */
function wcrw_warranty_length( $length = '' ) {
    $lengths = apply_filters( 'wcrw_warranty_length', [
        'limited'  => __( 'Limited', 'wc-return-warranty-management' ),
        'lifetime' => __( 'Lifetime', 'wc-return-warranty-management' )
    ] );

    if ( ! empty( $length ) ) {
        return isset( $lengths[$length] ) ? $lengths[$length] : '';
    }

    return $lengths;
}


/**
 * Warranty Length duration if included warranty
 *
 * @since 1.0.0
 *
 * @return string | Array
 */
function wcrw_warranty_length_duration( $duration = '' ) {
    $length_duration = [
        'days'   => __( 'Days', 'wc-return-warranty-management' ),
        'weeks'  => __( 'Weeks', 'wc-return-warranty-management' ),
        'months' => __( 'Months', 'wc-return-warranty-management' ),
        'years'  => __( 'Years', 'wc-return-warranty-management' )
    ];

    if ( ! empty( $duration ) ) {
        return isset( $length_duration[$duration] ) ? $length_duration[$duration] : '';
    }

    return $length_duration;
}

/**
 * Get warranty request type
 *
 * @since 1.0.0
 *
 * @return void
 */
function wcrw_warranty_request_type( $type = '' ) {
    $types = apply_filters( 'wcrw_warranty_request_types',  [
        'replacement' => __( 'Replacement', 'wc-return-warranty-management' ),
        'refund'      => __( 'Refund', 'wc-return-warranty-management' )
    ] );

    if ( ! empty( $type ) ) {
        return isset( $types[$type] ) ? $types[$type] : '';
    }

    return $types;

}

/**
 * Transform post request for rma settings
 *
 * @since 1.0.0
 *
 * @return void
 */
function wcrw_transform_warranty_settings( $request = [] ) {
    $data = [];

    if ( ! empty( $request ) ) {
        $data = [
            'label'           => !empty( $request['label'] ) ? $request['label'] : __( 'Warranty', 'wc-return-warranty-management' ),
            'type'            => !empty( $request['type'] ) ? $request['type'] : 'no_warranty',
            'length'          => '',
            'length_value'    => '',
            'length_duration' => '',
            'addon_settings'  => [],
        ];

        if ( 'included_warranty' == $data['type'] ) {
            $data['length']          = $request['length'];
            $data['length_value']    = $request['length_value'];
            $data['length_duration'] = $request['length_duration'];
            $data['addon_settings']  = [];

            if ( 'lifetime' == $data['length'] ) {
                $data['length_value']    = '';
                $data['length_duration'] = '';
            }
        }

        if ( 'addon_warranty' == $data['type'] ) {
            $addon_settings = [];

            if ( ! empty( $request['add_ons']['price'] ) ) {
                foreach ( $request['add_ons']['price'] as $key => $price ) {
                    $addon_settings[] = [
                        'price'    => $price,
                        'length'   => !empty( $request['add_ons']['length'][$key] ) ? $request['add_ons']['length'][$key] : '',
                        'duration' => !empty( $request['add_ons']['duration'][$key] ) ? $request['add_ons']['duration'][$key] : '',
                    ];
                }
            }

            $data['addon_settings'] = $addon_settings;
        }
    }

    return $data;
}


/**
 * Get warranty settings
 *
 * @since 1.0.0
 *
 * @return void
 */
function wcrw_get_warranty_settings( $product_id = 0 ) {
    $settings = [];
    $default      = [
        'from'            => 'default',
        'label'           => __( 'Warranty', 'wc-return-warranty-management' ),
        'type'            => 'no_warranty',
        'length'          => '',
        'length_value'    => '',
        'length_duration' => '',
        'addon_settings'  => []
    ];

    if ( $product_id ) {
        $override_default = get_post_meta( $product_id, '_wcrw_override_default_warranty', true );

        if ( 'yes' == $override_default ) {
            $settings = get_post_meta( $product_id, '_wcrw_product_warranty', true );
            $settings['from'] = 'product';
        } else {
            $admin_settings = get_option( 'wcrw_default_warranty', [] );
            $settings       = wcrw_transform_warranty_settings( $admin_settings );
        }

        $settings = wcrw_parse_args( $settings, $default );
    } else {
        $admin_settings   = get_option( 'wcrw_default_warranty', [] );
        $default_settings = wcrw_transform_warranty_settings( $admin_settings );
        $settings         = wcrw_parse_args( $default_settings, $default );
    }

    return $settings;
}

/**
 * Get duration value
 *
 * @since 1.0.0
 *
 * @return void
 */
function wcrw_get_duration_value( $duration, $value = 0 ) {
    $unit = wcrw_warranty_length_duration( $duration );

    if ( 1 == $value ) {
        $unit = rtrim( $unit, 's' );
    }

    return $unit;
}

/**
 * Get the warranty validity date based on the order date and warranty duration
 *
 * @since 1.0.0
 *
 * @param string $order_date
 * @param int $warranty_duration
 * @param string $warranty_unit
 *
 * @return string
 */
function wcrw_get_warranty_date( $order_date, $warranty_duration, $warranty_unit ) {
    $order_time     = strtotime( $order_date );
    $expired_date   = false;

    $order_date = array(
        'month'     => date( 'n', $order_time ),
        'day'       => date( 'j', $order_time ),
        'year'      => date( 'Y', $order_time )
    );

    if ( $warranty_unit == 'days' ) {

        $expired_time = $order_time + $warranty_duration*86400;
        $expired_date = date( 'Y-m-d', $expired_time )." 23:59:59";
        $expired_time = strtotime( $expired_date );

    } elseif ( $warranty_unit == 'weeks' ) {

        $add = (86400 * 7) * $warranty_duration;
        $expired_time = $order_time + $add;
        $expired_date = date( 'Y-m-d', $expired_time )." 23:59:59";
        $expired_time = strtotime( $expired_date );

    } elseif ( $warranty_unit == 'months' ) {
        $warranty_day   = $order_date['day'];
        $warranty_month = $order_date['month'] + $warranty_duration;
        $warranty_year  = $order_date['year'] + ( $warranty_month / 12 );
        $warranty_month = $warranty_month % 12;

        if ( ( $warranty_month == 2 ) && ( $warranty_day > 28 ) ) $warranty_day = 29;

        if ( checkdate( $warranty_month, $warranty_day, $warranty_year ) ) {
            $expired_time = mktime( 23, 59, 59, $warranty_month, $warranty_day, $warranty_year );
        } else {
            $expired_time = mktime( 23, 59, 59, $warranty_month, ( $warranty_day - 1 ) , $warranty_year );
        }
    } elseif ( $warranty_unit == 'years' ) {
        $warranty_year = $order_date['year'] + $warranty_duration;

        if ( checkdate( $order_date['month'], $order_date['day'], $warranty_year ) ) {
            $expired_time = mktime( 23, 59, 59, $order_date['month'], $order_date['day'], $warranty_year );
        } else {
            $expired_time = mktime( 23, 59, 59, $order_date['month'], ($order_date['day'] - 1) , $warranty_year );
        }
    }

    if ( $expired_time ) {
        return date_i18n( get_option( 'date_format' ), $expired_time );
    }

    return '-';
}

/**
 * Get warranty request form fields
 *
 * @return array
 */
function wcrw_get_warranty_request_form_fields() {
    $mandatory_fileds = [
        [
            'label'   => __( 'Request for', 'wc-return-warranty-management' ),
            'id'      => 'type',
            'class'   => 'wcrw-warranty-request-type',
            'type'    => 'select',
            'options' => array_merge( [ '' => __( '-- Select type --', 'wc-return-warranty-management' ) ], wcrw_warranty_request_type() )
        ],
        [
            'label'       => __( 'Reason for request', 'wc-return-warranty-management' ),
            'id'          => 'reasons',
            'class'       => 'wcrw-warranty-request-reason',
            'type'        => 'textarea',
            'placeholder' => __( 'Write your reason for request', 'wc-return-warranty-management' )
        ],
    ];

    return apply_filters( 'wcrw_request_form_fields', $mandatory_fileds );
}

/**
 * Render request fields depending on type
 *
 * @since 1.0.0
 *
 * @return void
 */
function wcrw_render_request_form_field( $field ) {
    if ( empty( $field ) ) {
        return;
    }

    $default = [
        'label'         => '',
        'id'            => '',
        'class'         => 'wcrw-warranty-request-field',
        'type'          => 'text',
        'options'       => [],
        'default'       => '',
        'placeholder'   => '',
        'value'         => '',
        'row'           => '6',
        'wrapper_class' => ''
    ];

    $args = wp_parse_args( $field, $default );

    ob_start();
    switch ( $field['type'] ) {
        case 'text':
            ?>
            <label for="<?php echo $args['id']; ?>"><?php echo $args['label']; ?></label>
            <span class="woocommerce-input-wrapper <?php echo $args['wrapper_class']; ?>">
                <input type="text" class="<?php echo $args['class'] ?>" name="<?php echo $args['id']; ?>" id="<?php echo $args['id']; ?>" placeholder="<?php echo $args['placeholder']; ?>" value="<?php echo $args['value'] ?>">
            </span>
            <?php
            break;
        case 'textarea':
            ?>
            <label for="<?php echo $args['id']; ?>"><?php echo $args['label']; ?></label>
            <span class="woocommerce-input-wrapper">
                <textarea name="<?php echo $args['id']; ?>"  class="<?php echo $args['class'] ?>" id="<?php echo $args['id']; ?>" rows="<?php echo $args['row']; ?>" placeholder="<?php echo $args['placeholder']; ?>"><?php echo $args['value'] ?></textarea>
            </span>
            <?php
            break;
        case 'select':
            ?>
            <label for="<?php echo $args['id']; ?>"><?php echo $args['label']; ?></label>
            <span class="woocommerce-input-wrapper">
                <select name="<?php echo $args['id']; ?>" id="<?php echo $args['id']; ?>" class="<?php echo $args['class'] ?>">
                    <?php foreach ( $args['options'] as $key => $value ): ?>
                        <option value="<?php echo $key; ?>" <?php selected( $args['value'], $key ); ?>><?php echo $value; ?></option>
                    <?php endforeach ?>
                </select>
            </span>
            <?php
            break;
        default:
            do_action( 'wcrw_render_request_form_field', $field );
            break;
        return ob_get_clean();
    }
}

/**
 * Create warranty Requests
 *
 * @since 1.0.0
 *
 * @return void
 */
function wcrw_create_warranty_request( $postdata = [] ) {
    global $wpdb;

    $request_table     = $wpdb->prefix . 'wcrw_warranty_requests';
    $request_map_table = $wpdb->prefix . 'wcrw_request_product_map';

    $default = [
        'items'       => [],
        'order_id'    => 0,
        'customer_id' => get_current_user_id(),
        'type'        => '',
        'reasons'     => '',
        'status'      => 'new',
        'meta'        => [],
        'created_at'  => current_time( 'mysql' )
    ];

    $args = wcrw_parse_args( $postdata, $default );

    // If have any order
    if ( empty( $args['order_id'] ) ) {
        return new WP_Error( 'no-order-id', __( 'No order found', 'wc-return-warranty-management' ) );
    }

    // Checking if customer select any items for sending request
    if ( empty( $args['items'] ) ) {
        return new WP_Error( 'no-items', __( 'Please select any item for sending request', 'wc-return-warranty-management' ) );
    }

    // Check if type exist or not
    if ( empty( $args['type'] ) ) {
        return new WP_Error( 'no-type', __( 'Request type must be required', 'wc-return-warranty-management' ) );
    }

    $args = apply_filters( 'wcrw_warranty_request_postdata', $args, $postdata );

    $wpdb->insert(
        $request_table,
        [
            'order_id'    => $args['order_id'],
            'customer_id' => $args['customer_id'],
            'type'        => $args['type'],
            'status'      => $args['status'],
            'reasons'     => $args['reasons'],
            'meta'        => maybe_serialize( $args['meta'] ),
            'created_at'  => $args['created_at'],
        ],
        [ '%d', '%d', '%s', '%s', '%s', '%s', '%s' ]
    );

    $request_id = $wpdb->insert_id;

    foreach ( $args['items'] as $item ) {
        $wpdb->insert(
            $request_map_table,
            [
                'request_id' => $request_id,
                'product_id' => $item['product_id'],
                'quantity'   => $item['quantity'],
                'item_id'    => $item['item_id']
            ],
            [ '%d', '%d', '%d' ]
        );
    }

    if ( $request_id ) {
        do_action( 'wcrw_created_warranty_request', $request_id, $args, $postdata );
        return true;
    }

    return false;
}

/**
 * wc-return-warranty-management get warranty request
 *
 * @since 1.0.0
 *
 * @return void
 */
function wcrw_get_warranty_request( $data = [] ) {
    global $wpdb;

    $default = [
        'id'      => 0,
        'number'  => 20,
        'offset'  => 0,
        'orderby' => 'created_at',
        'order'   => 'desc',
        'count'   => false,
    ];

    $data              = wcrw_parse_args( $data, $default );
    $request_table     = $wpdb->prefix . 'wcrw_warranty_requests';
    $request_map_table = $wpdb->prefix . 'wcrw_request_product_map';
    $response          = [];

    if ( $data['count'] ) {
        $sql = "SELECT count('id') as total_count FROM {$request_table} as rt WHERE 1=1";
    } else {
        $sql = "SELECT rt.*, GROUP_CONCAT( rit.product_id SEPARATOR ',') AS 'products', GROUP_CONCAT( rit.quantity SEPARATOR ', ') AS 'quantity', GROUP_CONCAT( rit.item_id SEPARATOR ', ') AS 'item_id' FROM {$request_table} as rt INNER JOIN {$request_map_table} as rit ON rt.id=rit.request_id WHERE 1=1";
    }

    if ( ! empty( $data['type'] ) ) {
        $sql .= " AND rt.type='{$data['type']}'";
    }

    if ( ! empty( $data['customer_id'] ) ) {
        $sql .= " AND rt.customer_id='{$data['customer_id']}'";
    }

    if ( ! empty( $data['order_id'] ) ) {
        $sql .= " AND rt.order_id='{$data['order_id']}'";
    }

    if ( ! empty( $data['reasons'] ) ) {
        $sql .= " AND rt.reasons='{$data['reasons']}'";
    }

    if ( ! empty( $data['status'] ) ) {
        $sql .= " AND rt.status='{$data['status']}'";
    }

    if ( $data['id'] ) {
        $sql .= " AND rt.id='{$data['id']}'";
    }

    if ( ! $data['count'] ) {
        $sql .= " GROUP BY rt.id ORDER BY {$data['orderby']} {$data['order']} LIMIT {$data['offset']}, {$data['number']}";
    }

    if ( $data['count'] || $data['id'] ) {
        $result = $wpdb->get_row( $sql, ARRAY_A );

        if ( $result ) {
            if ( ! $data['count'] ) {
                return wcrw_transformer_warranty_request( $result );
            }
            return $result;
        }
    }

    $results = $wpdb->get_results( $sql, ARRAY_A );

    if ( ! empty( $results ) ) {
        foreach ( $results as $key => $result ) {
            $response[] = wcrw_transformer_warranty_request( $result );
        }
    }

    return $response;
}

/**
 * Update warranty requests
 *
 * @param array $data
 *
 * @return WP_Error | true
 */
function wcrw_update_warranty_request( $data = [] ) {
    global $wpdb;

    if ( empty( $data['id'] ) ) {
        return new WP_Error( 'no-request-id', __( 'No request id found', 'wc-return-warranty-management' ) );
    }

    $statuses      = wcrw_warranty_request_status();
    $request_table = $wpdb->prefix . 'wcrw_warranty_requests';

    $request = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$request_table} WHERE `id`=%d", $data['id'] ), ARRAY_A );
    $data    = wcrw_parse_args( $data, $request );

    if ( ! in_array( $data['status'], array_keys( $statuses ) ) ) {
        return new WP_Error( 'invalid-status', __( 'Invalid status', 'wc-return-warranty-management' ) );
    }

    $result = $wpdb->update( $request_table, $data, [ 'id' => $data['id'] ] );

    if ( ! $result ) {
        return new WP_Error( 'status-not-updated', __( 'Request not updated successfully', 'wc-return-warranty-management' ) );
    }

    do_action( 'wcrw_update_warranty_request', $data['id'], $request, $data );

    if ( $request['status'] != $data['status'] ) {
        /**
         * Update status action
         *
         * @param integer $request_id
         * @param string $old_status -> $request['status']
         * @param string $new_status -> $data['status']
         */
        do_action( 'wcrw_update_request_status', $data['id'], $request['status'], $data['status'] );
    }

    return $result;
}


/**
 * Transform warranty request items
 *
 * @since 1.0.0
 *
 * @return void
 */
function wcrw_transformer_warranty_request( $data ) {
    $items       = [];
    $product_ids = explode( ',', $data['products'] );
    $quantites   = explode( ',', $data['quantity'] );
    $item_ids    = explode( ',', $data['item_id'] );
    $order       = wc_get_order( $data['order_id'] );

    foreach ( $item_ids as $key => $item_id ) {
        $item = new WC_Order_Item_Product( $item_id );
        $product = wc_get_product( $item->get_product_id() );
        $image = wp_get_attachment_url( $product->get_image_id() );

        $items[] = [
            'id'             => $product->get_id(),
            'title'          => $product->get_title(),
            'thumbnail'      => $image ? $image : wc_placeholder_img_src(),
            'quantity'       => $quantites[$key],
            'url'            => $product->get_permalink(),
            'price'          => $order->get_item_subtotal( $item, false ),
            'item_id'        => $item_id,
            'order_quantity' => $item->get_quantity(),
        ];
    }

    if ( ! empty( $data['customer_id'] ) ) {
        $customer = get_user_by( 'id', $data['customer_id'] );
    } else {
        $customer = false;
    }

    return apply_filters( 'wcrw_get_warranty_single_data', [
        'id'          => $data['id'],
        'order_id'    => $data['order_id'],
        'customer' => [
            'billing' => [
                'first_name' => $order->get_billing_first_name(),
                'last_name' => $order->get_billing_last_name(),
                'email' => $order->get_billing_email(),
                'address' => $order->get_formatted_billing_address()
            ],
            'first_name' => $customer ? $customer->first_name : '',
            'last_name' => $customer ? $customer->last_name: '',
            'email' => $customer ? $customer->user_email: '',
            'id'   => $order->get_customer_id(),
            'ip_address'   => $order->get_customer_ip_address(),
            'user_agent' => $order->get_customer_user_agent()
        ],
        'items'       => $items,
        'type'        => $data['type'],
        'status'      => $data['status'],
        'reasons'     => $data['reasons'],
        'meta'        => maybe_unserialize( $data['meta'] ),
        'created_at'  => $data['created_at']
    ] );
}

/**
 * Get formatted request items
 *
 * @param array $items
 * @param boolean $with_refund_label
 *
 * @return string
 */
function wcrw_get_formatted_request_items( $items, $with_refund_label = false ) {
    $formatted_item = [];

    if ( ! empty( $items ) ) {
        foreach ( $items as $item ) {
            $refund_label = '';

            if ( $with_refund_label ) {
                $warranty_item = new WCRW_Warranty_Item( $item->get_id() );
            }
            $formatted_item[] = '<a href="' . $item['url'] . '">' . $item['title'] . '</a> &times; ' . $item['quantity'];
        }
    }

    if ( ! empty( $formatted_item ) ) {
        return implode( ', ', $formatted_item );
    }

    return;
}

/**
 * Get request status filter
 *
 * @since 1.0.0
 *
 * @return void
 */
function wcrw_get_request_status_count( $customer_id = 0 ) {
    global $wpdb;

    $statuses = array( 'all' => __( 'All', 'wc-return-warranty-management' ) ) + wcrw_warranty_request_status();
    $counts   = array();

    foreach ( $statuses as $status => $label ) {
        $counts[ $status ] = array( 'count' => 0, 'label' => $label );
    }

    if ( empty( $customer_id ) ) {
        $results = $wpdb->get_results( "SELECT count(`id`) as count, status FROM `{$wpdb->prefix}wcrw_warranty_requests` GROUP BY `status`", ARRAY_A );
    } else {
        $results = $wpdb->get_results(
                        $wpdb->prepare(
                            "SELECT count(`id`) as count, status FROM `{$wpdb->prefix}wcrw_warranty_requests` where `customer_id`='%d' GROUP BY `status`",
                            intval( $customer_id )
                        ),
                    ARRAY_A );
    }

    foreach ( $results as $row ) {
        if ( array_key_exists( $row['status'], $counts ) ) {
            $counts[ $row['status'] ]['count'] = (int) $row['count'];
        }
        $counts['all']['count'] += (int) $row['count'];
    }

    return $counts;
}

/**
 * Create request note
 *
 * @since 1.0.0
 *
 * @return void
 */
function wcrw_add_request_note( $data = [] ) {
    global $wpdb;

    $note_table     = $wpdb->prefix . 'wcrw_request_notes';

    $default = [
        'request_id' => 0,
        'note'       => '',
        'created_at' => current_time( 'mysql' )
    ];

    $args = wcrw_parse_args( $data, $default );

    if ( empty( $args['request_id'] ) ) {
        return new WP_Error( 'no-request', __( 'No request found', 'wc-return-warranty-management' ), [ 'status' => 403 ] );
    }

    if (  empty( $args['note'] ) ) {
        return new WP_Error( 'no-notes', __( 'Note field is empty. Please write something', 'wc-return-warranty-management' ), [ 'status' => 403 ] );
    }

    $wpdb->insert(
        $note_table,
        [
            'request_id' => $args['request_id'],
            'note'       => $args['note'],
            'created_at' => $args['created_at'],
        ],
        [ '%d', '%s', '%s' ]
    );

    $note_id = $wpdb->insert_id;

    if ( $note_id ) {
        $args['id'] = $note_id;
        return $args;
    }

    return false;
}

/**
 * Delete requests
 *
 * @since 1.0.0
 *
 * @return void
 */
function wcrw_delete_warranty_request( $request_id = 0 ) {
    global $wpdb;

    if ( ! $request_id ) {
        return new WP_Error( 'no-request-id', __( 'No Request found for delete', 'wc-return-warranty-management' ), array( 'status' => 403 ) );
    }

    $main_row = $wpdb->delete( $wpdb->prefix . 'wcrw_warranty_requests', [ 'id' => $request_id ], [ '%d' ] );

    if ( $main_row ) {
        $join_rows = $wpdb->delete( $wpdb->prefix . 'wcrw_request_product_map', [ 'request_id' => $request_id ], [ '%d' ] );
    }

    if ( ! $main_row ) {
        return new WP_Error( 'request-not-deleted', __( 'Request not deleted, Try again', 'wc-return-warranty-management' ), array( 'status' => 403 ) );
    }

    return true;
}

/**
 * Get notes for request
 *
 * @since 1.0.0
 *
 * @return void
 */
function wcrw_get_request_notes( $data = [] ) {
    global $wpdb;

    $default = [
        'id'         => 0,
        'orderby'    => 'created_at',
        'order'      => 'desc',
        'request_id' => 0,
    ];

    $data       = wcrw_parse_args( $data, $default );
    $note_table = $wpdb->prefix . 'wcrw_request_notes';
    $response   = [];

    $sql = "SELECT * FROM {$note_table} WHERE 1=1";

    if ( ! empty( $data['request_id'] ) ) {
        $sql .= " AND `request_id`={$data['request_id']}";
    }

    $sql .= " ORDER BY created_at DESC";

    if ( ! empty( $data['id'] ) ) {
        $result = $wpdb->get_row( $sql, ARRAY_A );
    } else {
        $result = $wpdb->get_results( $sql, ARRAY_A );
    }

    return $result;
}

/**
 * Delete request note
 *
 * @since 1.0.0
 *
 * @return void
 */
function wcrw_delete_request_note( $id = 0 ) {
    global $wpdb;

    if ( empty( $id ) ) {
        return new WP_Error( 'no-request', __( 'No request id found', 'wc-return-warranty-management' ), [ 'status' => 403 ] );
    }

    $response = $wpdb->delete( $wpdb->prefix . 'wcrw_request_notes', [ 'id' => $id ], [ '%d' ] );

    if ( ! $response ) {
        return new WP_Error( 'not-deleted', __( 'Request note not deleted. Please try again', 'wc-return-warranty-management' ), [ 'status' => 403 ] );
    }

    return true;
}

function wcrw_order_has_any_item_warranty( $order ) {
    if ( ! $order ) {
        return false;
    }

    $has_warranty = false;

    foreach ( $order->get_items() as $key => $item ) {
        $product = $item->get_product();
        $warranty_item = new WCRW_Warranty_Item( $item->get_id() );

        if ( $warranty_item->has_warranty() ) {
            return true;
        }
    }

    return false;
}

