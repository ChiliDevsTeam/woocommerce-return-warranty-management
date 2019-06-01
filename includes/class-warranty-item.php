<?php
if ( !defined( 'ABSPATH' ) ) exit;

/**
* Class for warranty items
*/
class WCRW_Warranty_Item {

    /**
     * Hold item id
     *
     * @var integer
     */
    public $id = 0;

    /**
     * Warranty type
     *
     * no warranty|Add on warranty| included warranty
     *
     * @var string
     */
    public $type = '';

    /**
     * Hold item warranty data
     *
     * @var array
     */
    public $data = [];

    /**
     * Load autometically when class initiate
     *
     * @param integer $item [item id for an order]
     *
     * @since 1.0.0
     */
    public function __construct( $item ) {
        $warranty   = wc_get_order_item_meta( $item, '_wcrw_item_warranty', true );
        $selected   = wc_get_order_item_meta( $item, '_wcrw_item_warranty_selected', true );

        $this->id = $item;

        if ( ! $warranty ) {
            $this->type = 'no_warranty';
            return;
        }

        if ( 'included_warranty' === $warranty['type'] ) {
            $this->type = 'included_warranty';
            $this->data = [
                'length' => $warranty['length'],
                'value'  => $warranty['length_value'],
                'duration' => $warranty['length_duration'],
            ];
            return;
        }

        if ( 'addon_warranty' === $warranty['type'] ) {
            $this->type = 'addon_warranty';

            $selected_warranty = $warranty['addon_settings'][$selected];

            $this->data = [
                'length' => 'limited',
                'value'  => $selected_warranty['length'],
                'duration' => $selected_warranty['duration'],
            ];
            return;
        }
    }

    /**
     * Get order id from item id
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function get_order_id() {
        global $wpdb;

        return (int) $wpdb->get_var(
            $wpdb->prepare(
                "SELECT order_id FROM {$wpdb->prefix}woocommerce_order_items WHERE order_item_id = %d",
                $this->id
            )
        );
    }

    /**
     * Get type
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function get_type() {
        return $this->type;
    }

    /**
     * Get Warranty data for item
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function get_data() {
        return $this->data;
    }

    /**
     * Get type
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function get_expiry_date_string() {
        $date_string = '';

        if ( ! empty( $this->data ) ) {
            $order = wc_get_order( $this->get_order_id() );
            $date_string = wcrw_get_warranty_date( $order->get_date_completed(), $this->data['value'], $this->data['duration'] );
        }

        return $date_string;
    }

    /**
     * Get expiry date
     *
     * @return date string
     */
    public function get_expiry_date() {
        $order          = wc_get_order( $this->get_order_id() );
        $date_timestamp = 0;
        $completed_date = $order->get_date_completed() ? $order->get_date_completed()->date( 'Y-m-d H:i:s' ) : false;

        if ( $completed_date && ! empty( $this->data ) ) {
            $date_timestamp = strtotime( $completed_date . ' +'. $this->data['value'] .' '. $this->data['duration'] );
        }

        return $date_timestamp;
    }

    /**
     * Get remaining qunatity after placeing request
     *
     * @since 1.0.0
     *
     * @return void
     */
    public function get_remaining_quantity() {
        global $wpdb;

        $request_map_table = $wpdb->prefix . 'wcrw_request_product_map';
        $qty                = wc_get_order_item_meta( $this->id, '_qty', true );
        $product_id         = wc_get_order_item_meta( $this->id, '_product_id', true );
        $order_id           = $this->get_order_id();

        $sql    = "SELECT SUM( quantity ) as total_qty FROM {$request_map_table} WHERE item_id='%d'";
        $result = $wpdb->get_row( $wpdb->prepare( $sql, $this->id ), ARRAY_A );

        return (int)$qty - (int)$result['total_qty'];
    }

    /**
     * Check if the item has warranty. So that customer
     * can send warranty request
     *
     * First check if included warranty with lifetime then return -- true;
     * Secont check if warranty have time and expiry date then return -- true
     * otherwise -- false
     *
     * @return bool
     */
    public function has_warranty() {
        $has_warranty  = false;
        $remaining_qty = $this->get_remaining_quantity();

        if ( $remaining_qty < 1 ) {
            return $has_warranty;
        }

        if ( $this->type == 'included_warranty' ) {
            if ( 'lifetime' === $this->data['length'] ) {
                return true;
            }
        }

        $now    = current_time( 'timestamp' );
        $expiry = $this->get_expiry_date();

        if ( $expiry && ( $now < $expiry ) ) {
            $has_warranty = true;
        }

        return $has_warranty;
    }


}
