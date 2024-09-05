<?php

class Class_Hooks {


	protected static $_instance = null;

	function __construct() {
		add_action( 'init', array( $this, 'register_every_things' ) );
		add_action( 'admin_init', array( $this, 'custom_meta_box' ) );
		add_action( 'save_post', array( $this, 'save_meta_box_data' ) );
		add_filter( 'manage_product_posts_columns', array( $this, 'add_custom_columns' ) );
		add_action( 'manage_product_posts_custom_column', array( $this, 'add_columns_content' ), 10, 2 );

		add_action( 'admin_menu', array( $this, 'create_sub_menu_page' ) );
	}


	function all_order_report() {
		$table = new Ordered_List_Table();

		echo '<div class="wrap"><h2>Order List Table</h2>';
		echo '<form action="edit.php?post_type=product&page=order_report" method="get">';
		$table->prepare_items();
		$table->display();
		echo '</form>';
		echo '</div>';
	}


	function add_custom_columns( $columns ) {

		unset( $columns['date'] );
		$columns['product_price'] = esc_html__( 'Price', 'kazi' );
		$columns['product_stock'] = esc_html__( 'Stock', 'kazi' );

		return $columns;
	}

	function add_columns_content( $column, $post_id ) {
		if ( $column == 'product_price' ) {
			$product_price = get_post_meta( $post_id, 'product_price', true );
			printf( '<strong><div>%s</div></strong>', esc_html( $product_price ) );
		}
		if ( $column == 'product_stock' ) {
			$product_stock = get_post_meta( $post_id, 'product_stock', true );
			printf( '<strong><div>%s</div></strong>', esc_html( $product_stock ) );
		}
	}

	function save_meta_box_data() {
		global $post;
		$stock              = $_POST['product_stock'] ?? '';
		$price              = $_POST['product_price'] ?? '';
		$product_buy_price  = $_POST['product_buy_price'] ?? '';
		$product_sell_price = $_POST['product_sell_price'] ?? '';

		if ( $post_id = $post->ID ) {
			update_post_meta( $post_id, 'product_stock', $stock );
			update_post_meta( $post_id, 'product_price', $price );
			update_post_meta( $post_id, 'product_buy_price', $product_buy_price );
			update_post_meta( $post_id, 'product_sell_price', $product_sell_price );
		}

	}

	function create_sub_menu_page() {
		add_submenu_page( 'edit.php?post_type=product', 'Order Reports', 'Order Reports', 'manage_options', 'order_report', array( $this, 'all_order_report' ), 2 );
	}


	function custom_meta_box() {
		add_meta_box( 'product_price', esc_html__( 'Product Price', 'kazi-cart' ), array( $this, 'price_meta_box_callback' ), 'product', 'normal', 'low' );
		add_meta_box( 'product_sell_price', esc_html__( 'Product Sell Price', 'kazi-cart' ), array( $this, 'sell_price_meta_box_callback' ), 'product', 'normal', 'low' );
		add_meta_box( 'product_stock', esc_html__( 'Product Stock', 'kazi-cart' ), array( $this, 'stock_meta_box_callback' ), 'product', 'normal', 'low' );
		add_meta_box( 'product_buy_price', esc_html__( 'Product Buy Price', 'kazi-cart' ), array( $this, 'buy_price_meta_box_callback' ), 'product', 'normal', 'low' );
	}

	function buy_price_meta_box_callback() {
		global $post;

		$product_buy_price = get_post_meta( $post->ID, 'product_buy_price', true ) ?? '';
		echo '<input type="number" name="product_buy_price" value="' . $product_buy_price . '">';
	}

	function stock_meta_box_callback() {
		global $post;

		$stock = get_post_meta( $post->ID, 'product_stock', true ) ?? '';
		echo '<input type="number" name="product_stock" value="' . $stock . '">';
	}

	function sell_price_meta_box_callback() {
		global $post;

		$product_sell_price = get_post_meta( $post->ID, 'product_sell_price', true ) ?? '';
		echo '<input type="number" name="product_sell_price" value="' . $product_sell_price . '">';
	}

	function price_meta_box_callback() {
		global $post;
		$price = get_post_meta( $post->ID, 'product_price', true ) ?? '';
		echo '<input type="text" name="product_price" value="' . $price . '">';
	}


	function register_every_things() {

		$args = array(
			'labels'        => array(
				'name'          => esc_html__( 'Products', 'kazi-cart' ),
				'singular_name' => esc_html__( 'Product', 'kazi-cart' ),
				'add_new'       => esc_html__( 'Add New Product', 'kazi-cart' ),
			),
			'public'        => true,
			'menu_position' => 3,
			'has_archive'   => true,
			'supports'      => array( 'title', 'editor', 'thumbnail' ),
		);

		register_post_type( 'product', $args );

		register_taxonomy( 'product_cat', [ 'product' ], [
			'labels'            => array(
				'name'          => esc_html__( 'Product Category', 'kazi-cart' ),
				'singular_name' => esc_html__( 'Product Category', 'kazi-cart' )
			),
			'hierarchical'      => true,
			'show_admin_column' => true,
			'rewrite'           => array( 'slug' => 'product_cat' ),
		] );

		create_db_table();

	}


	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

}

Class_Hooks::instance();
