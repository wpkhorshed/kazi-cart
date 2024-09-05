<?php

defined( "ABSPATH" ) || exit;

if ( ! class_exists( 'Kazi_Settings_Page' ) ) {
	class Kazi_Settings_Page {
		protected static $_instance = null;

		function __construct() {
			add_action( 'admin_menu', array( $this, 'create_admin_menu_page' ) );
			add_action( 'admin_init', array( $this, 'register_admin_settings_fields' ) );
		}


		function register_admin_settings_fields() {
			if ( ! empty( $_FILES ) && isset( $_FILES['kazi_store_logo'] ) ) {
				$uploaded_image = wp_handle_upload( $_FILES['kazi_store_logo'], array( 'test_form' => false ) );
				if ( isset( $uploaded_image['url'] ) ) {
					update_option( 'kazi_store_logo', $uploaded_image['url'] );
				}
			}
			add_settings_section( 'kazi_settings', '', array( $this, 'render_settings_section' ), 'kazi-settings' );

			$fields = array(
				'kazi_store_name'           => array(
					'title'    => esc_html__( 'Store Name', 'kazi-cart' ),
					'type'     => 'text',
					'subtitle' => esc_html__( 'Please enter your store name.', 'kazi-cart' ),
				),
				'kazi_store_number'         => array(
					'title'    => esc_html__( 'Mobile Number', 'kazi-cart' ),
					'type'     => 'text',
					'subtitle' => esc_html__( 'Please enter your store mobile.', 'kazi-cart' ),
				),
				'kazi_store_address'        => array(
					'title'    => esc_html__( 'Store Address', 'kazi-cart' ),
					'type'     => 'text',
					'subtitle' => esc_html__( 'Please enter your store address.', 'kazi-cart' ),
				),
				'kazi_store_term_condition' => array(
					'title'    => esc_html__( 'Terms & Conditions', 'kazi-cart' ),
					'type'     => 'textarea',
					'subtitle' => esc_html__( 'Please enter your business T&C.', 'kazi-cart' ),
				),

			);

			foreach ( $fields as $field_id => $field_data ) {

				add_settings_field(
					$field_id,
					$field_data['title'],
					array( $this, 'render_setting_fields' ),
					'kazi-settings',
					'kazi_settings',
					array(
						'field_id'    => $field_id,
						'field_type'  => $field_data['type'],
						'placeholder' => $field_data['placeholder'] ?? '',
						'subtitle'    => $field_data['subtitle'] ?? '',
					)
				);
				register_setting( 'kazi_settings', $field_id );
			}

		}

		function render_setting_fields( $args ) {

			$field_id    = $args['field_id'];
			$field_type  = $args['field_type'];
			$field_value = get_option( $field_id );
			$placeholder = $args['placeholder'];
			$subtitle    = isset( $args['subtitle'] ) ? sanitize_text_field( $args['subtitle'] ) : '';

			if ( $field_type == 'checkbox' ) {
				echo '<input type="checkbox" id="' . esc_attr( $field_id ) . '" name="' . esc_attr( $field_id ) . '" value="yes" ' . checked( 'yes', $field_value, false ) . ' /><p>' . esc_html( $subtitle ) . '</p>';
			} elseif ( $field_type == 'textarea' ) {
				echo '<textarea name="kazi_store_term_condition" id="kazi_store_term_condition" cols="33" rows="2">' . esc_attr( $field_value ) . '</textarea>';
			} else {
				echo '<input type="' . esc_attr( $field_type ) . '" id="' . esc_attr( $field_id ) . '" placeholder="' . esc_attr( $placeholder ) . '" name="' . esc_attr( $field_id ) . '" value="' . esc_attr( $field_value ) . '" /><p>' . esc_html( $subtitle ) . '</p>';
			}

		}

		function render_settings_section() {
			echo '<h2>Store Info</h2>';
		}

		function create_admin_menu_page() {
			add_submenu_page( 'edit.php?post_type=product', 'Store Settings', 'Settings', 'manage_options', 'kazi-settings', array( $this, 'store_settings_page' ) );
		}


		function store_settings_page() {
			$uploaded_image_url = get_option( 'kazi_store_logo' ); ?>

            <div class='wrap'>
                <form method='post' action='options.php' enctype='multipart/form-data'>
					<?php
					settings_fields( 'kazi_settings' );
					do_settings_sections( 'kazi-settings' ); ?>
                    <label for="kazi_logo"><h2><?php echo esc_html__( 'Business Logo', 'kazi-cart' ) ?></h2></label>
                    <input type="file" name="kazi_store_logo" id="kazi_store_logo"/>
					<?php if ( $uploaded_image_url ) : ?>
                        <img src="<?php echo esc_attr( $uploaded_image_url ); ?>" alt="Uploaded Image" style="max-width: 150px; max-height: 80px;"/>
					<?php endif; ?>
                    <p class='submit'>
                        <input name='submit' type='submit' id='submit' class='button-primary' value='<?php _e( "Save Changes" ) ?>'/>
                    </p>
                </form>
            </div>
			<?php
		}


		public static function instance() {
			if ( is_null( self::$_instance ) ) {
				self::$_instance = new self();
			}

			return self::$_instance;
		}
	}
}
Kazi_Settings_Page::instance();

