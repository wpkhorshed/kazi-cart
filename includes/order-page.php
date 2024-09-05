<?php

if ( ! class_exists( 'WP_List_Table' ) ) {
	require_once( ABSPATH . 'wp-admin/includes/class-wp-list-table.php' );
}

class Ordered_List_Table extends WP_List_Table {

	private $table_data;

	function get_all_orders( $search = '' ) {
		global $wpdb;

//		if ( $search ) {
//			return $wpdb->get_results( "SELECT * FROM kazi_orders WHERE customer_name Like '%{$search}%' OR customer_mobile Like '%{$search}%' ", ARRAY_A );
//		}

		return $wpdb->get_results( "SELECT * FROM kazi_orders ORDER BY id DESC", ARRAY_A );

	}

	function get_columns() {
		$columns = array(
			'id'     => esc_html__( 'ID', 'kazi-cart' ),
			'name'   => esc_html__( 'Customer Name', 'kazi-cart' ),
			'mobile' => esc_html__( 'Mobile', 'kazi-cart' ),
			'total'  => esc_html__( 'Ordered Total', 'kazi-cart' ),
			'date'   => esc_html__( 'Date', 'kazi-cart' ),
			'action' => esc_html__( 'Action', 'kazi-cart' )
		);

		return $columns;
	}

	function prepare_items() {

//		$search = ( isset( $_REQUEST['s'] ) ) ? wp_unslash( trim( $_REQUEST['s'] ) ) : '';
//		if ( $search ) {
//			$this->table_data = $this->get_all_orders( $search );
//		} else {
//			$this->table_data = $this->get_all_orders();
//		}

		$this->table_data      = $this->get_all_orders();
		$columns               = $this->get_columns();
		$hidden                = array();
		$sortable              = array();
		$this->_column_headers = array( $columns, $hidden, $sortable );

		$per_page     = 10;
		$current_page = $this->get_pagenum();
		$total_items  = count( $this->table_data );

		$this->set_pagination_args( array(
			'total_items' => $total_items,
			'per_page'    => $per_page,
			'total_pages' => ceil( $total_items / $per_page )
		) );
		$this->table_data = array_slice( $this->table_data, ( ( $current_page - 1 ) * $per_page ), $per_page );
		$this->items      = $this->table_data;
	}

	function column_id( $item ) {
		static $index = 1;

		return sprintf( '<div>%s</div>', $index ++ );
	}

	function column_name( $item ) {
		$name = $item['customer_name'] ?? '';

		return sprintf( '<div>%s</div>', $name );
	}

	function column_mobile( $item ) {
		$mobile = $item['customer_mobile'] ?? '';

		return sprintf( '<div>%s</div>', $mobile );
	}

	function column_total( $item ) {

		$products    = $item['product_desc'] ? json_decode( $item['product_desc'] ) : '';
		$total_price = 0;

		foreach ( $products as $product_id => $quantity ) {

			$price       = get_post_meta( $product_id, 'product_price', true );
			$total       = ( $price * $quantity );
			$total_price += $total;

		}

		return sprintf( '<div>%s Tk</div>', $total_price );
	}

	function column_date( $item ) {
		$datetime = $item['datetime'] ?? '';

		return sprintf( '<div>%s</div>', $datetime );
	}

	function column_action( $item ) {
		$order_id = $item['id'] ?? '';
		ob_start() ?>
        <div class="kazi-wrap">
            <div class="kazi-actions">
                <div class="edit" data-order-id="<?php echo esc_attr( $order_id ); ?>"><?php echo esc_html__( 'Edit', 'kazi-cart' ) ?></div>
                <div class="view" data-order-id="<?php echo esc_attr( $order_id ); ?>"><?php echo esc_html__( 'View', 'kazi-cart' ) ?></div>
            </div>

            <div class="kazi-tooltip">Tooltip</div>
        </div>

		<?php
		return ob_get_clean();
	}

}

