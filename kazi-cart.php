<?php
/*
	Plugin Name: kazi Cart
	Description: kazi Cart.
	Version: 1.0.0
	Text Domain: kazi-cart
	Author: kazi
	License: GPLv2 or later
	License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

defined( 'ABSPATH' ) || exit;
defined( 'PLUGIN_URL' ) || define( 'PLUGIN_URL', WP_PLUGIN_URL . '/' . plugin_basename( dirname( __FILE__ ) ) . '/' );
defined( 'PLUGIN_DIR' ) || define( 'PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
defined( 'PLUGIN_FILE' ) || define( 'PLUGIN_FILE', plugin_basename( __FILE__ ) );
defined( 'PLUGIN_VERSION' ) || define( 'PLUGIN_VERSION', '1.0.0' );

if ( ! class_exists( 'Kazi_Cart_Main' ) ) {
	class Kazi_Cart_Main {

		protected static $_instance = null;
		protected static $_script_version = null;

		function __construct() {

			$this->define_scripts();
			$this->file_includes();

			add_shortcode( 'kazi_cart_products', array( $this, 'shortcode_products' ) );
			add_shortcode( 'kazi_cart', array( $this, 'shortcode_carts' ) );

			add_action( 'wp_ajax_add_to_cart', array( $this, 'handle_add_to_cart' ) );
			add_action( 'wp_ajax_nopriv_add_to_cart', array( $this, 'handle_add_to_cart' ) );

			add_action( 'wp_ajax_remove_cart', array( $this, 'handle_remove_cart' ) );
			add_action( 'wp_ajax_nopriv_remove_cart', array( $this, 'handle_remove_cart' ) );

			add_action( 'wp_ajax_increase_item', array( $this, 'handle_increase_item' ) );
			add_action( 'wp_ajax_nopriv_increase_item', array( $this, 'handle_increase_item' ) );

			add_action( 'wp_ajax_decrease_item', array( $this, 'handle_decrease_item' ) );
			add_action( 'wp_ajax_nopriv_decrease_item', array( $this, 'handle_decrease_item' ) );

			add_action( 'wp_ajax_input_quantity', array( $this, 'handle_input_quantity' ) );
			add_action( 'wp_ajax_nopriv_input_quantity', array( $this, 'handle_input_quantity' ) );


			add_action( 'init', array( $this, 'register_kazi_invoice_endpoint' ) );
			add_filter( 'template_redirect', array( $this, 'handle_invoice_endpoint' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'enqueue_invoice_styles' ) );
			add_action( 'wp_print_styles', array( $this, 'print_custom_endpoint_style' ) );

		}


        function print_custom_endpoint_style() {
			wp_print_styles( 'kazi-invoice-front' );
		}

		function enqueue_invoice_styles() {
			global $wp_query;

			if ( $wp_query->query_vars['name'] == 'kazi-invoice' ) {
				wp_register_style( 'kazi-invoice-front', PLUGIN_URL . 'assets/invoice/css/style.css', array(), self::$_script_version );
				wp_enqueue_style( 'kazi-invoice-front' );
			}
		}


		function handle_invoice_endpoint() {

			global $wp_query;

			if ( $wp_query->query_vars['name'] == 'kazi-invoice' ) {
				include_once PLUGIN_DIR . 'template/invoice.php';
				exit;
			}
		}


		function register_kazi_invoice_endpoint() {
			add_rewrite_endpoint( 'kazi-invoice', EP_ROOT );
		}


		function handle_input_quantity() {
			$product_id  = $_POST['product_id'] ?? '';
			$input_value = $_POST['input_value'] ?? '';

			$cart = isset( $_COOKIE['kazi_cart'] ) ? json_decode( stripslashes( $_COOKIE['kazi_cart'] ), true ) : array();
			if ( $product_id && $input_value ) {
				if ( isset( $cart[ $product_id ] ) ) {
					$cart[ $product_id ] = $input_value;
				}
			}
			$set = setcookie( 'kazi_cart', json_encode( $cart ), time() + ( 86400 * 30 ), "/" );
			if ( $set ) {
				wp_send_json_success( [ 'message' => esc_html__( 'Increase item', 'kazi-cart' ) ] );
			}
		}

		function handle_decrease_item() {
			$product_id = $_POST['product_decrement_id'] ?? '';
			$cart       = isset( $_COOKIE['kazi_cart'] ) ? json_decode( stripslashes( $_COOKIE['kazi_cart'] ), true ) : array();

			if ( isset( $cart[ $product_id ] ) ) {
				$cart[ $product_id ] --;
			}

			$set = setcookie( 'kazi_cart', json_encode( $cart ), time() + ( 86400 * 30 ), "/" );
			if ( $set ) {
				wp_send_json_success( [ 'message' => esc_html__( 'Increase item', 'kazi-cart' ) ] );
			}
		}

		function handle_increase_item() {
			$product_id = $_POST['product_increment_id'] ?? '';
			$cart       = isset( $_COOKIE['kazi_cart'] ) ? json_decode( stripslashes( $_COOKIE['kazi_cart'] ), true ) : array();

			if ( isset( $cart[ $product_id ] ) ) {
				$cart[ $product_id ] ++;
			}

			$set = setcookie( 'kazi_cart', json_encode( $cart ), time() + ( 86400 * 30 ), "/" );
			if ( $set ) {
				wp_send_json_success( [ 'message' => esc_html__( 'Increase item', 'kazi-cart' ) ] );
			}
		}

		function handle_remove_cart() {
			$product_id = $_POST['product_id'] ?? '';
			$cart       = isset( $_COOKIE['kazi_cart'] ) ? json_decode( stripslashes( $_COOKIE['kazi_cart'] ), true ) : array();

			if ( isset( $cart[ $product_id ] ) ) {
				unset( $cart[ $product_id ] );
			}

			$remove = setcookie( 'kazi_cart', json_encode( $cart ), time() + ( 86400 * 30 ), "/" );
			if ( $remove ) {
				wp_send_json_success( [ 'message' => esc_html__( 'Cart Deleted', 'kazi-cart' ) ] );
			}

		}

		function handle_add_to_cart() {

			$product_id = $_POST['product_id'] ?? '';
			$cart       = isset( $_COOKIE['kazi_cart'] ) ? json_decode( stripslashes( $_COOKIE['kazi_cart'] ), true ) : array();

			if ( isset( $cart[ $product_id ] ) ) {
				$cart[ $product_id ] ++;
			} else {
				$cart[ $product_id ] = 1;
			}

			$set = setcookie( 'kazi_cart', json_encode( $cart ), time() + ( 86400 * 30 ), "/" );
			if ( $set ) {
				wp_send_json_success( [ 'message' => esc_html__( 'Cart Added', 'kazi-cart' ) ] );
			}
		}

		function display_all_products() {

			$terms    = get_terms();
			$category = isset( $_GET['kazi_category'] ) ? $_GET['kazi_category'] : ''; ?>


            <form action="" method="get">
                <label for="kazi-category"></label>
                <select name="kazi_category" id="kazi-category">
                    <option value="">Select One</option>
					<?php foreach ( $terms as $key => $value ) { ?>
                        <option value="<?php echo $value->slug; ?>" <?php if ( $category == $value->slug ) {
							echo 'selected';
						} ?>><?php echo $value->name; ?></option>
					<?php } ?>
                </select>
                <button class="submit-button">Search</button>
            </form>
			<?php

			$args = array(
				'post_type'      => 'product',
				'posts_per_page' => - 1,
			);

			$the_query = new WP_Query( $args );

			if ( ! empty( $category ) ) {
				$args = array(
					'post_type'      => 'product',
					'posts_per_page' => - 1,
					'tax_query'      => array(
						array(
							'taxonomy' => 'product_cat',
							'field'    => 'slug',
							'terms'    => $category,
							'operator' => 'IN',
						),
					),
				);

				$the_query = new WP_Query( $args );
			} ?>

            <div class="kazi-products">

				<?php if ( $the_query->have_posts() ) : ?>
                    <div class="custom-grid">
						<?php
						$counter = 0;
						while ( $the_query->have_posts() ) : $the_query->the_post();
							if ( $counter % 3 == 0 ) {
								if ( $counter > 0 ) {
									echo '</div>';
								}
								echo '<div class="row">';
							}
							$sell_price = get_post_meta( get_the_ID(), 'product_sell_price', true );
							$price      = get_post_meta( get_the_ID(), 'product_price', true );

							?>
                            <div class="kazi-product">
                                <div class="kazi-post-thumbnail">
                                    <a href="<?php the_permalink(); ?>">
										<?php the_post_thumbnail( 'medium' ); ?>
                                    </a>
                                </div>
                                <div class="kazi-title"><?php the_title(); ?></div>
                                <div class="kazi-price"><b>Price : </b><?php echo $sell_price . '  ' . '<del>' . $price . '</del>'; ?></div>
                                <div class="kazi-stock">Stock : <?php echo $stock = get_post_meta( get_the_ID(), 'product_stock', true ); ?></div>
								<?php

								if ( $stock < 1 ) { ?>
                                    <div class="kazi-stock-out"><?php echo esc_html__( 'Out of stock', 'kazi-cart' ) ?></div>
								<?php } else { ?>
                                    <div class="kazi-add-cart" data-product-id="<?php echo get_the_ID(); ?>"><?php echo esc_html__( 'Add To Cart', 'kazi-cart' ) ?></div>
								<?php } ?>
                            </div>
							<?php
							$counter ++;
						endwhile;

						if ( $counter % 3 != 0 ) {
							echo '</div>';
						}
						?>
                    </div>
				<?php endif; ?>

				<?php wp_reset_postdata(); ?>

            </div>

			<?php
		}


		function display_all_carts() {

			$product_ids      = isset( $_COOKIE['kazi_cart'] ) ? json_decode( stripslashes( $_COOKIE['kazi_cart'] ), true ) : array();
			$customer_name    = $_POST['customer_name'] ?? '';
			$customer_mobile  = $_POST['customer_mobile'] ?? '';
			$customer_address = $_POST['customer_address'] ?? '';
			$submit_info      = $_POST['submit_info'] ?? '';


			if ( $submit_info == 'yes' ) {
				$cookie_name  = "kazi_customer_info";
				$cookie_value = array(
					"name"    => $customer_name,
					"mobile"  => $customer_mobile,
					"address" => $customer_address
				);

				$cookie_value_json = json_encode( $cookie_value );
				$expiration_time   = time() + ( 30 * 24 * 60 * 60 );
				setcookie( $cookie_name, $cookie_value_json, $expiration_time, "/" );
			}

			$customer_info = isset( $_COOKIE['kazi_customer_info'] ) ? json_decode( stripslashes( $_COOKIE['kazi_customer_info'] ), true ) : array();

			$name    = $customer_info['name'] ?? '';
			$mobile  = $customer_info['mobile'] ?? '';
			$address = $customer_info['address'] ?? '';

			?>

            <div class="kazi-customer-info">
                <h4>Customer Info</h4>
                <div class="form-container">
                    <form action="" method="post">
                        <div class="group">
                            <label for="customer_name">NAME*</label>
                            <input type="text" value="<?php echo $name ?? ''; ?>" id="customer_name" name="customer_name" placeholder="Enter your name" required autocomplete="off">
                        </div>
                        <div class="group">
                            <label for="customer_mobile">PHONE NUMBER*</label>
                            <input type="tel" value="<?php echo $mobile ?? ''; ?>" id="customer_mobile" name="customer_mobile" placeholder="Enter your Phone Number" required autocomplete="off">
                        </div>
                        <div class="group">
                            <label for="customer_address">ADDRESS*</label>
                            <input type="text" value="<?php echo $address ?? ''; ?>" id="email" name="customer_address" placeholder="Enter your address" autocomplete="off">
                        </div>
                        <button type="submit" name="submit_info" value="yes">Save</button>
                    </form>
                </div>
            </div>
			<?php

			if ( empty( $product_ids ) ) {
				echo '<h2>You have no cart added.</h2>';
			}


			if ( $product_ids && is_array( $product_ids ) ) {
				echo '<h3>Cart Items</h3>';
				foreach ( $product_ids as $product_id => $quantity ):
					$price = (int) get_post_meta( $product_id, 'product_price', true );
					?>
                    <div class="kazi-carts">
                        <div class="kazi-cart kazi-cart-wrap">
                            <div class="product-img">
								<?php $thumbnail_url = get_the_post_thumbnail_url( $product_id, 'full' ) ?? ''; ?>
                                <img src="<?php echo $thumbnail_url; ?>" alt="Product Image">
                            </div>

                            <div class="pricing">
                                <div class="product-name"><?php echo get_the_title( $product_id ); ?></div>
                                <div class="price">Price : <?php echo ( $price * $quantity ) . ' TK'; ?></div>
                                <div class="quantity">
                                    <div class="button" data-product-decrement-id="<?php echo esc_attr( $product_id ); ?>" id="kazi-decrement-button" data-input-counter-decrement="counter-input">-</div>
                                    <div class="input-quntity"><input id="kazi-quantity-input" data-input-product-id="<?php echo esc_attr( $product_id ); ?>" value="<?php echo esc_attr( $quantity ); ?>" type="text">
                                    </div>
                                    <div class="button" data-product-increment-id="<?php echo esc_attr( $product_id ); ?>" id="kazi-increment-button" data-input-counter-increment="counter-input">+</div>
                                </div>
                                <div class="kazi-remove-cart " data-product-id="<?php echo esc_attr( $product_id ); ?>">Remove</div>
                            </div>
                        </div>
                    </div>

				<?php
				endforeach;

				$site_url = admin_url( "/invoice" );
				printf( '<div class="kazi-place-order"><a target="_blank" href="%s">%s</a></div>', esc_url( $site_url ), esc_html__( 'Place Order', 'kazi-cart' ) );
			}
		}

		function shortcode_products() {
			ob_start();
			$this->display_all_products();

			return ob_get_clean();
		}

		function shortcode_carts() {
			ob_start();
			$this->display_all_carts();

			return ob_get_clean();
		}

		function file_includes() {
			require_once 'includes/class-hooks.php';
			require_once 'includes/functions.php';
			require_once 'includes/order-page.php';
			require_once 'includes/class-admin-menu.php';
		}

		function admin_scripts() {
			wp_enqueue_style( 'kazi-cart-admin', PLUGIN_URL . 'assets/admin/css/style.css', array(), self::$_script_version );
			wp_enqueue_script( 'kazi-cart-admin', plugins_url( 'assets/admin/js/scripts.js', __FILE__ ), array( 'jquery' ), self::$_script_version );
			wp_localize_script( 'kazi-cart-admin', 'kazi_cart', $this->localize_scripts() );
		}


		function front_scripts() {
			wp_enqueue_style( 'kazi-cart-front', PLUGIN_URL . 'assets/front/css/style.css', array(), self::$_script_version );
			wp_enqueue_script( 'kazi-cart-front', plugins_url( '/assets/front/js/scripts.js', __FILE__ ), array( 'jquery' ), self::$_script_version );
			wp_localize_script( 'kazi-cart-front', 'kazi_cart', $this->localize_scripts() );
		}

		function localize_scripts() {
			return apply_filters( 'cart_filters_localize_scripts', array(
				'ajaxurl' => admin_url( 'admin-ajax.php' ),
			) );
		}

		function define_scripts() {
			add_action( 'wp_enqueue_scripts', array( $this, 'front_scripts' ) );
			add_action( 'admin_enqueue_scripts', array( $this, 'admin_scripts' ) );
		}

		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}

			return self::$_instance;
		}

	}

	Kazi_Cart_Main::instance();
}



