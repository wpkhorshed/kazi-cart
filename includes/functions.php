<?php


function create_db_table() {

	if ( ! function_exists( 'maybe_create_table' ) ) {
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
	}

	$sql_attributes_table = "CREATE TABLE IF NOT EXISTS kazi_orders(
			id int(100) NOT NULL AUTO_INCREMENT,
			customer_name VARCHAR(255),
			customer_mobile VARCHAR(255),
			customer_address VARCHAR(255),
			product_desc VARCHAR(255),
			datetime DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    		PRIMARY KEY  (id)
		)";

	maybe_create_table( 'kazi_orders', $sql_attributes_table );
}


function kazi_insert_order_into_db( $product_desc, $customer_name, $customer_mobile, $customer_address ) {
	global $wpdb;

	$data = array(
		'customer_name'    => $customer_name,
		'customer_mobile'  => $customer_mobile,
		'customer_address' => $customer_address,
		'product_desc'     => json_encode( $product_desc ),
	);

	$format = array( '%s', '%s', '%s', '%s' );

	$insert = $wpdb->insert( 'kazi_orders', $data, $format );
	if ( $insert ) {
		return $wpdb->insert_id;
	}

	return false;
}

function kazi_get_all_orders() {
	global $wpdb;

	$orders = $wpdb->get_results( "SELECT * FROM kazi_orders WHERE ORDER BY DESC ",ARRAY_A );

	return $orders;
}
