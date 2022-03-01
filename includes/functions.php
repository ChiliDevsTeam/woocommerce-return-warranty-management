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
        'new'        => __( 'New', 'wc-return-warranty' ),
        'processing' => __( 'Processing', 'wc-return-warranty' ),
        'completed'  => __( 'Completed', 'wc-return-warranty' ),
        'rejected'   => __( 'Rejected', 'wc-return-warranty' ),
        'reviewing'  => __( 'Reviewing', 'wc-return-warranty' ),
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
        'no_warranty'       => __( 'No Warranty', 'wc-return-warranty' ),
        'included_warranty' => __( 'Included Warranty', 'wc-return-warranty' ),
        'addon_warranty'    => __( 'Price base Warranty', 'wc-return-warranty' )
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
        'limited'  => __( 'Limited', 'wc-return-warranty' ),
        'lifetime' => __( 'Lifetime', 'wc-return-warranty' )
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
        'days'   => __( 'Days', 'wc-return-warranty' ),
        'weeks'  => __( 'Weeks', 'wc-return-warranty' ),
        'months' => __( 'Months', 'wc-return-warranty' ),
        'years'  => __( 'Years', 'wc-return-warranty' )
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
        'replacement' => __( 'Replacement', 'wc-return-warranty' ),
        'refund'      => __( 'Refund', 'wc-return-warranty' ),
        'cancel'      => __( 'Cancel', 'wc-return-warranty' ),
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
            'label'           => !empty( $request['label'] ) ? $request['label'] : __( 'Warranty', 'wc-return-warranty' ),
            'type'            => !empty( $request['type'] ) ? $request['type'] : 'no_warranty',
            'length'          => '',
            'length_value'    => '',
            'length_duration' => '',
            'hide_warranty'   => 'no',
            'addon_settings'  => [],
        ];

        if ( 'included_warranty' == $data['type'] ) {
            $data['length']          = $request['length'];
            $data['length_value']    = $request['length_value'];
            $data['length_duration'] = $request['length_duration'];
            $data['hide_warranty']   = ! empty( $request['hide_warranty'] ) ? $request['hide_warranty'] : 'no';
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

    return apply_filters( 'wcrw_transform_warranty_settings', $data );
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
        'label'           => __( 'Warranty', 'wc-return-warranty' ),
        'type'            => 'no_warranty',
        'length'          => '',
        'length_value'    => '',
        'length_duration' => '',
        'hide_warranty'   => 'no',
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

    return apply_filters( 'wcrw_get_warranty_settings_data', $settings, $product_id );
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
        return new WP_Error( 'no-order-id', __( 'No order found', 'wc-return-warranty' ) );
    }

    // Checking if customer select any items for sending request
    if ( empty( $args['items'] ) ) {
        return new WP_Error( 'no-items', __( 'Please select any item for sending request', 'wc-return-warranty' ) );
    }

    // Check if type exist or not
    if ( empty( $args['type'] ) ) {
        return new WP_Error( 'no-type', __( 'Request type must be required', 'wc-return-warranty' ) );
    }

    $general_settings      = get_option( 'wcrw_basic' );
    $default_refund_status = ! empty( $general_settings['default_refund_status'] ) ? $general_settings['default_refund_status'] : 'new';
    $args['status']        = $default_refund_status;

    $args = apply_filters( 'wcrw_warranty_request_postdata', $args, $postdata );

    $meta_field_errors = [];
    $extra_field_data  = wcrw_get_form_fields_data();


    foreach ( $extra_field_data as $meta_field ) {
        // Bell out if not proper type of meta field
        if ( in_array( $meta_field['type'] , apply_filters( 'wcrw_except_request_form_fields', [ 'html' ] ) ) ) {
            continue;
        }

        $meta_value = ! empty( $args[$meta_field['name']] ) ? $args[$meta_field['name']] : '';

        if ( $meta_field['settings']['required'] ) {
            if ( empty( $meta_value )
                || ( 'select' == $meta_field['type'] && $meta_value == '-1' )
                || ( 'checkbox' == $meta_field['type'] && $meta_value == 'no' )
            ) {
                $meta_field_errors[] = sprintf( "%s %s", $meta_field['label'], __( 'field is required', 'wc-return-warranty' ) );
            }
        }

        if ( 'select' == $meta_field['type'] ) {
            $options = wp_list_pluck( $meta_field['settings']['options'], 'label', 'value' );

            $args['meta'][] = [
                'key'   => $meta_field['name'],
                'label' => $meta_field['label'],
                'value' => ! empty( $options[$meta_value] ) ? $options[$meta_value] : ''
            ];
        } else {
            $args['meta'][] = [
                'key'   => $meta_field['name'],
                'label' => $meta_field['label'],
                'value' => $meta_value
            ];
        }
    }

    $errors = apply_filters( 'wcrw_create_warranty_request_error', $meta_field_errors, $extra_field_data );

    if ( ! empty( $errors ) ) {
        return new WP_Error( 'required-meta-data', $errors[0] );
    }

    $args['meta'] = apply_filters( 'wcrw_request_meta_data', $args['meta'], $extra_field_data, $args );

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
function wcrw_get_warranty_request( $args = [] ) {
    global $wpdb;

    $default = [
        'id'      => 0,
        'number'  => 20,
        'offset'  => 0,
        'orderby' => 'id',
        'order'   => 'desc',
        'count'   => false,
    ];

    $data              = wp_parse_args( $args, $default );
    $request_table     = $wpdb->prefix . 'wcrw_warranty_requests';
    $request_map_table = $wpdb->prefix . 'wcrw_request_product_map';
    $response          = [];

    if ( $data['count'] ) {
        $sql = "SELECT count('id') as total_count FROM {$request_table} as rt WHERE 1=1";
    } else {
        $sql = "SELECT rt.*, rt.`id` as `id`, rt.`created_at` as `created_at`, GROUP_CONCAT( rit.product_id SEPARATOR ',') AS 'products', GROUP_CONCAT( rit.quantity SEPARATOR ', ') AS 'quantity', GROUP_CONCAT( rit.item_id SEPARATOR ', ') AS 'item_id' FROM {$request_table} as rt INNER JOIN {$request_map_table} as rit ON rt.id=rit.request_id WHERE 1=1";
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
        return new WP_Error( 'no-request-id', __( 'No request id found', 'wc-return-warranty' ) );
    }

    $statuses      = wcrw_warranty_request_status();
    $request_table = $wpdb->prefix . 'wcrw_warranty_requests';

    $request = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM {$request_table} WHERE `id`=%d", $data['id'] ), ARRAY_A );
    $data    = wcrw_parse_args( $data, $request );

    if ( ! in_array( $data['status'], array_keys( $statuses ) ) ) {
        return new WP_Error( 'invalid-status', __( 'Invalid status', 'wc-return-warranty' ) );
    }

    $result = $wpdb->update( $request_table, $data, [ 'id' => $data['id'] ] );

    if ( ! $result ) {
        return new WP_Error( 'status-not-updated', __( 'Request not updated successfully', 'wc-return-warranty' ) );
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

    if ( empty( $order ) ) {
        return [];
    }

    foreach ( $item_ids as $key => $item_id ) {
        $item    = new WC_Order_Item_Product( $item_id );
        if ( $item->get_product_id() ) {
            $product = wc_get_product( $item->get_product_id() );
            $image   = !empty( $product ) ? wp_get_attachment_url( $product->get_image_id() ) : '';
            $items[] = [
                'id'             => $product->get_id(),
                'title'          => $product->get_title(),
                'thumbnail'      => $image ? $image : wc_placeholder_img_src(),
                'quantity'       => $quantites[$key],
                'url'            => $product->get_permalink(),
                'price'          => $order->get_line_subtotal( $item, true ),
                'item_id'        => $item_id,
                'order_quantity' => $item->get_quantity(),
            ];
        } else {
            $items[] = [
                'id'             => $item->get_product_id(),
                'title'          => $item->get_name(),
                'thumbnail'      => wc_placeholder_img_src(),
                'quantity'       => $quantites[$key],
                'url'            => '#',
                'price'          => $order->get_item_subtotal( $item, false ),
                'item_id'        => $item_id,
                'order_quantity' => $item->get_quantity(),
            ];
        }
    }

    if ( ! empty( $data['customer_id'] ) ) {
        $customer = get_user_by( 'id', $data['customer_id'] );
    } else {
        $customer = false;
    }

    return apply_filters( 'wcrw_get_warranty_single_data', [
        'id'       => $data['id'],
        'order_id' => $data['order_id'],
        'customer' => [
            'billing' => [
                'first_name' => $order->get_billing_first_name(),
                'last_name'  => $order->get_billing_last_name(),
                'email'      => $order->get_billing_email(),
                'address'    => $order->get_formatted_billing_address()
            ],
            'first_name' => $customer ? $customer->first_name : '',
            'last_name'  => $customer ? $customer->last_name: '',
            'email'      => $customer ? $customer->user_email: '',
            'id'         => $order->get_customer_id(),
            'ip_address' => $order->get_customer_ip_address(),
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

    $statuses = array( 'all' => __( 'All', 'wc-return-warranty' ) ) + wcrw_warranty_request_status();
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
        return new WP_Error( 'no-request', __( 'No request found', 'wc-return-warranty' ), [ 'status' => 403 ] );
    }

    if (  empty( $args['note'] ) ) {
        return new WP_Error( 'no-notes', __( 'Note field is empty. Please write something', 'wc-return-warranty' ), [ 'status' => 403 ] );
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
        return new WP_Error( 'no-request-id', __( 'No Request found for delete', 'wc-return-warranty' ), array( 'status' => 403 ) );
    }

    $main_row = $wpdb->delete( $wpdb->prefix . 'wcrw_warranty_requests', [ 'id' => $request_id ], [ '%d' ] );

    if ( $main_row ) {
        $join_rows = $wpdb->delete( $wpdb->prefix . 'wcrw_request_product_map', [ 'request_id' => $request_id ], [ '%d' ] );
    }

    if ( ! $main_row ) {
        return new WP_Error( 'request-not-deleted', __( 'Request not deleted, Try again', 'wc-return-warranty' ), array( 'status' => 403 ) );
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
        return new WP_Error( 'no-request', __( 'No request id found', 'wc-return-warranty' ), [ 'status' => 403 ] );
    }

    $response = $wpdb->delete( $wpdb->prefix . 'wcrw_request_notes', [ 'id' => $id ], [ '%d' ] );

    if ( ! $response ) {
        return new WP_Error( 'not-deleted', __( 'Request note not deleted. Please try again', 'wc-return-warranty' ), [ 'status' => 403 ] );
    }

    return true;
}

/**
 * Check order has any item
 *
 * @param object $order
 *
 * @return boolean
 */
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

/**
 * Get request form builder form fileds
 *
 * @since 1.0.3
 *
 * @return void
 */
function wcrw_get_form_fields() {
    return apply_filters( 'wcrw_request_form_fields', [
        [
            'label' => __( 'Text input', 'wc-return-warranty' ),
            'type' => 'text',
            'settings' => [
                'description'  => '',
                'class'        => '',
                'id'           => '',
                'wrapperClass' => '',
                'size'         => '',
                'required'     => false,
                'placeholder'  => __( 'Text Field', 'wc-return-warranty' ),
            ]
        ],
        [
            'label' => __( 'Textarea', 'wc-return-warranty' ),
            'type' => 'textarea',
            'settings' => [
                'description'  => '',
                'class'        => '',
                'id'           => '',
                'wrapperClass' => '',
                'size'         => '',
                'required'     => false,
                'row'          => '4',
                'placeholder'  => __( 'Textarea Field', 'wc-return-warranty' ),
            ]
        ],
        [
            'label' => __( 'Checkbox', 'wc-return-warranty' ),
            'type' => 'checkbox',
            'settings' => [
                'description'  => '',
                'class'        => '',
                'id'           => '',
                'wrapperClass' => '',
                'required'     => false,
            ]
        ],
        [
            'label' => __( 'Select Dropdown', 'wc-return-warranty' ),
            'type' => 'select',
            'settings' => [
                'options'      => [],
                'emptyOption'  => __( 'Select a option', 'wc-return-warranty' ),
                'description'  => '',
                'class'        => '',
                'id'           => '',
                'wrapperClass' => '',
                'required'     => false,
            ]
        ],
        [
            'label' => __( 'Html', 'wc-return-warranty' ),
            'type' => 'html',
            'settings' => [
                'description'     => __( 'Lorem ipsum dolor sit amet, consectetur adipisicing elit. Officia, blanditiis.', 'wc-return-warranty' ),
                'class'           => '',
                'id'              => '',
                'wrapperClass'    => '',
                'headingFontSize' => '20',
                'paraFontSize'    => '14'
            ]
        ],
    ]);
}

/**
 * Get form fields data
 *
 * @since 1.1.0
 *
 * @return void
 */
function wcrw_get_form_fields_data() {
    $default_data = [
        [
            'label' => __( 'Reason for request', 'wc-return-warranty' ),
            'name'  => 'request_reasons',
            'type'  => 'textarea',
            'settings' => [
                'description'  => '',
                'class'        => '',
                'id'           => '',
                'wrapperClass' => '',
                'size'         => '',
                'required'     => false,
                'row'          => '4',
                'placeholder'  => __( 'Write your valid reasons', 'wc-return-warranty' ),
            ]
        ]
    ];

    return get_option( 'wcrw_request_form_data', $default_data );
}

/**
 * Get warranty request form fields
 *
 * @return array
 */
function wcrw_get_warranty_request_form_fields() {
    $general_settings  = get_option( 'wcrw_basic' );
    $settings_types    = ! empty( $general_settings['default_return_request_type'] ) ? $general_settings['default_return_request_type'] : [ 'replacement', 'refund' ];
    $allowed_types = [];

    foreach ( wcrw_warranty_request_type() as $key => $value ) {
        if ( in_array( $key, $settings_types ) ) {
            $allowed_types[$key] = $value;
        }
    }

    $mandatory_fileds = [
        [
            'label'   => __( 'Request for', 'wc-return-warranty' ),
            'name'    => 'type',
            'id'      => 'type',
            'class'   => 'wcrw-warranty-request-type',
            'type'    => 'select',
            'options' => array_merge( [ '' => __( '-- Select type --', 'wc-return-warranty' ) ], $allowed_types )
        ]
    ];

    $formatted_fields_array = [];
    $form_builder_fields    = wcrw_get_form_fields_data();

    foreach ( $form_builder_fields as $fields_array ) {
        $options = [];
        if ( in_array( $fields_array['type'], ['select', 'multiselect', 'multicheck' ] ) && ! empty( $fields_array['settings']['options'] ) ) {
            if ( ! empty( $fields_array['settings']['emptyOption'] ) ) {
                $options[-1] = $fields_array['settings']['emptyOption'];
            }

            foreach ( $fields_array['settings']['options'] as $option_array ) {
                $options[ $option_array['value'] ] = $option_array['label'];
            }
        }

        $formatted_fields_array[] = [
            'label'             => $fields_array['label'],
            'name'              => $fields_array['name'],
            'description'       => ! empty( $fields_array['settings']['description'] ) ? $fields_array['settings']['description'] : '',
            'id'                => $fields_array['settings']['id'],
            'class'             => $fields_array['settings']['class'],
            'type'              => $fields_array['type'],
            'options'           => $options,
            'default'           => '',
            'required'          => isset( $fields_array['settings']['required'] ) ? (bool)$fields_array['settings']['required'] : false,
            'multiple'          => isset( $fields_array['settings']['multiple'] ) ? (bool)$fields_array['settings']['multiple'] : false,
            'placeholder'       => ! empty( $fields_array['settings']['placeholder'] ) ? $fields_array['settings']['placeholder'] : '',
            'row'               => ! empty( $fields_array['settings']['row'] ) ? $fields_array['settings']['row'] : '',
            'wrapper_class'     => $fields_array['settings']['wrapperClass'],
            'min'               => ! empty( $fields_array['settings']['min'] ) ? $fields_array['settings']['min'] : '',
            'max'               => ! empty( $fields_array['settings']['max'] ) ? $fields_array['settings']['max'] : '',
            'step'              => ! empty( $fields_array['settings']['step'] ) ? $fields_array['settings']['step'] : 'any',
            'heading_font_size' => ! empty( $fields_array['settings']['headingFontSize'] ) ? $fields_array['settings']['headingFontSize'] : 'h2',
            'para_font_size'    => ! empty( $fields_array['settings']['paraFontSize'] ) ? $fields_array['settings']['paraFontSize'] : '20',
        ];
    }

    $all_fields = array_merge( $mandatory_fileds, $formatted_fields_array );

    return apply_filters( 'wcrw_get_request_form_fields', $all_fields, $mandatory_fileds, $formatted_fields_array  );
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
        'name'          => '',
        'description'   => '',
        'id'            => '',
        'class'         => 'wcrw-warranty-request-field',
        'type'          => 'text',
        'options'       => [],
        'default'       => '',
        'required'      => false,
        'placeholder'   => '',
        'value'         => '',
        'row'           => '6',
        'wrapper_class' => '',
        'min'           => '',
        'max'           => '',
        'step'          => 'any'
    ];

    $args = wp_parse_args( $field, $default );

    ob_start();
    switch ( $args['type'] ) {
        case 'text':
            ?>
            <label for="<?php echo $args['id']; ?>">
                <?php echo $args['label']; ?>
                <?php if ( $args['required']): ?>
                    <span class="required">*</span>
                <?php endif ?>
            </label>
            <span class="woocommerce-input-wrapper <?php echo $args['wrapper_class']; ?>">
                <input type="text" class="<?php echo $args['class'] ?>" name="<?php echo $args['name']; ?>" id="<?php echo $args['id']; ?>" placeholder="<?php echo $args['placeholder']; ?>" value="<?php echo $args['value'] ?>">
            </span>
            <?php if ( ! empty( $args['description'] ) ): ?>
                <span>
                    <em><?php echo $args['description']; ?></em>
                </span>
            <?php endif ?>
            <?php
            break;
        case 'textarea':
            ?>
            <label for="<?php echo $args['id']; ?>">
                <?php echo $args['label']; ?>
                <?php if ( $args['required']): ?>
                    <span class="required">*</span>
                <?php endif ?>
            </label>
            <span class="woocommerce-input-wrapper">
                <textarea name="<?php echo $args['name']; ?>"  class="<?php echo $args['class'] ?>" id="<?php echo $args['id']; ?>" rows="<?php echo $args['row']; ?>" placeholder="<?php echo $args['placeholder']; ?>"><?php echo $args['value'] ?></textarea>
            </span>
            <?php if ( ! empty( $args['description'] ) ): ?>
                <span>
                    <em><?php echo $args['description']; ?></em>
                </span>
            <?php endif ?>
            <?php
            break;
        case 'select':
            ?>
            <label for="<?php echo $args['id']; ?>">
                <?php echo $args['label']; ?>
                <?php if ( $args['required']): ?>
                    <span class="required">*</span>
                <?php endif ?>
            </label>
            <span class="woocommerce-input-wrapper">
                <select name="<?php echo $args['name']; ?>" id="<?php echo $args['id']; ?>" class="<?php echo $args['class'] ?>">
                    <?php foreach ( $args['options'] as $key => $value ): ?>
                        <option value="<?php echo $key; ?>" <?php selected( $args['value'], $key ); ?>><?php echo $value; ?></option>
                    <?php endforeach ?>
                </select>
            </span>
            <?php if ( ! empty( $args['description'] ) ): ?>
                <span>
                    <em><?php echo $args['description']; ?></em>
                </span>
            <?php endif ?>
            <?php
            break;
        case 'checkbox':
            ?>
            <label for="<?php echo $args['name']; ?>">
                <input type="hidden" name="<?php echo $args['name']; ?>" value="no">
                <input type="checkbox" class="<?php echo $args['class'] ?> checkbox" name="<?php echo $args['name']; ?>" id="<?php echo $args['name']; ?>" value="yes">
                <span><?php echo $args['label']; ?></span>
                <?php if ( $args['required']): ?>
                    <span class="required">*</span>
                <?php endif ?>
            </label>
            <?php if ( ! empty( $args['description'] ) ): ?>
                <span>
                    <em><?php echo $args['description']; ?></em>
                </span>
            <?php endif ?>
            <?php
            break;
        case 'html':
            $para_custom_style = ! empty( $args['para_font_size' ] ) ? 'style="font-size:'. $args['para_font_size'] . 'px"' : '';
            $heading_custom_style = ! empty( $args['heading_font_size' ] ) ? 'style="font-size:'. $args['heading_font_size'] . 'px"' : '';
            ?>
            <div class="<?php echo $args['class']; ?>" id="<?php echo $args['id']; ?>">
                <?php if ( ! empty( $args['label'] ) ): ?>
                    <h2 <?php echo $heading_custom_style; ?>><?php echo $args['label']; ?></h2>
                <?php endif ?>
                <?php if ( ! empty( $args['description'] ) ): ?>
                    <p <?php echo $para_custom_style; ?>><?php echo $args['description'];?></p>
                <?php endif ?>
            </div>
            <?php
            break;

        default:
            do_action( 'wcrw_render_request_form_field', $field, $args );
            break;
        return ob_get_clean();
    }
}

/**
 * Translactions fpr WC Return and Warranty
 *
 * @since 1.1.0
 *
 * @param string $domain
 * @param string $language_dir
 *
 * @return array
 */
function wcrw_get_translations_plugin_domain( $domain, $language_dir = null ) {

    if ( $language_dir == null ) {
        $language_dir      = WCRW_ASSETS . '/languages/';
    }

    $languages     = get_available_languages( $language_dir );
    $get_site_lang = is_admin() ? get_user_locale() : get_locale();
    $mo_file_name  = $domain . '-' . $get_site_lang;
    $translations  = [];

    if ( in_array( $mo_file_name, $languages ) && file_exists( $language_dir . $mo_file_name . '.mo' ) ) {
        $mo = new MO();
        if ( $mo->import_from_file( $language_dir . $mo_file_name . '.mo' ) ) {
            $translations = $mo->entries;
        }
    }

    return [
        'header'       => isset( $mo ) ? $mo->headers : '',
        'translations' => $translations,
    ];
}

/**
 * Jed-formatted localization data.
 *
 * @since 1.1.0
 *
 * @param  string $domain Translation domain.
 *
 * @return array
 */
function wcrw_get_jed_locale_data( $domain, $language_dir = null ) {
    $plugin_translations = wcrw_get_translations_plugin_domain( $domain, $language_dir );
    $translations = get_translations_for_domain( $domain );

    $locale = array(
        'domain'      => $domain,
        'locale_data' => array(
            $domain => array(
                '' => array(
                    'domain' => $domain,
                    'lang'   => is_admin() ? get_user_locale() : get_locale(),
                ),
            ),
        ),
    );

    if ( ! empty( $translations->headers['Plural-Forms'] ) ) {
        $locale['locale_data'][ $domain ]['']['plural_forms'] = $translations->headers['Plural-Forms'];
    } else if ( ! empty( $plugin_translations['header'] ) ) {
        $locale['locale_data'][ $domain ]['']['plural_forms'] = $plugin_translations['header']['Plural-Forms'];
    }

    $entries = array_merge( $plugin_translations['translations'], $translations->entries );

    foreach ( $entries as $msgid => $entry ) {
        $locale['locale_data'][ $domain ][ $msgid ] = $entry->translations;
    }

    return $locale;
}

/**
 * Upload handler
 *
 * @param string $filename
 * @param string $file_url
 *
 * @return integer attachement_id
 */
function wcrw_handle_file_upload( $filename, $file_url ) {
    $upload_file = wp_upload_bits( $filename, null, file_get_contents( $file_url ) );

    if ( ! $upload_file['error'] ) {
        $wp_filetype = wp_check_filetype( $filename, null );

        $attachment = array(
            'post_mime_type' => $wp_filetype['type'],
            'post_parent'    => 0,
            'post_title'     => preg_replace( '/\.[^.]+$/', '', $filename ),
            'post_content'   => '',
            'post_status'    => 'inherit'
        );
        $attachment_id = wp_insert_attachment( $attachment, $upload_file['file'] );

        if ( ! is_wp_error( $attachment_id ) ) {
            require_once( ABSPATH . "wp-admin" . '/includes/image.php' );
            $attachment_data = wp_generate_attachment_metadata( $attachment_id, $upload_file['file'] );
            wp_update_attachment_metadata( $attachment_id,  $attachment_data );
            return $attachment_id;
        }
    }

    return 0;
}

/**
 * Check has pro version isntalled or not
 *
 * @return boolean
 */
function wcrw_has_pro() {
    return class_exists( 'WC_Return_Warranty_Pro' );
}
