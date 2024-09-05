<?php

defined( "ABSPATH" ) || exit;

include_once PLUGIN_DIR . '/includes/functions.php';

$products      = isset( $_COOKIE['kazi_cart'] ) ? json_decode( stripslashes( $_COOKIE['kazi_cart'] ), true ) : array();
$customer_info = isset( $_COOKIE['kazi_customer_info'] ) ? json_decode( stripslashes( $_COOKIE['kazi_customer_info'] ), true ) : array();

$customer_name    = $customer_info['name'] ?? '';
$customer_phone   = $customer_info['mobile'] ?? '';
$customer_address = $customer_info['address'] ?? '';

global $wp_query;


//if ( empty( $products ) || empty( $customer_info ) ) {
//	echo esc_html__( 'Empty Products or Customer info!', 'kazi-cart' );
//	die();
//}

if ( $products ) {
	foreach ( $products as $product_id => $quantity ) {
		$old_stock     = get_post_meta( $product_id, 'product_stock', true );
		$current_stock = $old_stock - $quantity;
		update_post_meta( $product_id, 'product_stock', $current_stock );
	}
}

$insert_id = '';

if ( ! empty( $customer_info ) ) {
	$insert_id = kazi_insert_order_into_db( $products, $customer_name, $customer_phone, $customer_address );
	if ( $insert_id ) {
		$remove = setcookie( 'kazi_customer_info', json_encode( $customer_info ), time() - ( 30 * 24 * 60 * 60 ), "/" );
		$remove = setcookie( 'kazi_cart', json_encode( $products ), time() - ( 86400 * 30 ), "/" );
	} else {
		echo esc_html__( 'Your Order is not recorded. Please order again for record your order.', 'kazi-cart' );
		die();
	}
}

$store_name           = get_option( 'kazi_store_name' ) ?? '';
$store_mobile         = get_option( 'kazi_store_number' ) ?? '';
$store_address        = get_option( 'kazi_store_address' ) ?? '';
$store_term_condition = get_option( 'kazi_store_term_condition' ) ?? '';
$store_image_url      = get_option( 'kazi_store_logo' );

?>

<!--<!DOCTYPE html>-->
<!--<html class="no-js" lang="en">-->
<!--<head>-->
<!--    <meta charset="utf-8">-->
<!--    <meta http-equiv="x-ua-compatible" content="ie=edge">-->
<!--    <meta name="viewport" content="width=device-width, initial-scale=1">-->
<!--    <meta name="author" content="Laralink">-->
<!--    <title>--><?php //echo wp_kses( __( 'Maya Cyber World| Invoice', 'kazi-cart' ), array() ); ?><!--</title>-->
<!---->
<style>

<?php //include_once PLUGIN_DIR . 'assets/invoice/css/style.css'; ?>
</style>

<!--</head>-->


<body class="kazi-invoice">
<div class="tm_container">
    <div class="tm_invoice_wrap">
        <div class="tm_invoice tm_style1" id="tm_download_section">
            <div class="tm_invoice_in">
                <div class="tm_invoice_head tm_align_center tm_mb20">
                    <div class="tm_invoice_left">
                        <div class="tm_logo"><img src="<?php echo esc_attr( $store_image_url ); ?>" alt="<?php echo esc_html__( 'Logo', 'kazi-cart' ) ?>"></div>
                    </div>
                    <div class="tm_invoice_right tm_text_right">
                        <div class="tm_primary_color tm_f30 tm_text_uppercase"><?php echo esc_html__( 'Invoice', 'kazi-cart' ) ?></div>
                    </div>
                </div>
                <div class="tm_invoice_info tm_mb20">
                    <div class="tm_invoice_seperator"></div>
                    <p>Invoice ID: # <?php echo $insert_id; ?>&nbsp;</p>
                    <div class="tm_invoice_info_list">
                        <p class="tm_invoice_date tm_m0"><?php echo esc_html__( 'Date: ', 'kazi-cart' ) ?> <b class="tm_primary_color"><?php echo esc_html( current_datetime()->format( 'Y-m-d H:i:s A' ) ); ?></b></p>
                    </div>
                </div>
                <div class="tm_invoice_head tm_mb10">
                    <div class="tm_invoice_left">
                        <p class="tm_mb2"><b class="tm_primary_color"><?php echo esc_html__( 'Invoice To: ', 'kazi-cart' ) ?></b></p>
                        <p>
							<?php echo esc_html( $customer_name ) ?>
                            <br>
							<?php echo esc_html( $customer_phone ) ?>
                            <br>
							<?php echo esc_html( $customer_address ); ?>

                        </p>
                    </div>
                    <div class="tm_invoice_right tm_text_right">
                        <p class="tm_mb2"><b class="tm_primary_color"><?php echo esc_html__( 'Pay To: ', 'kazi-cart' ) ?></b></p>
                        <p>
							<?php echo esc_html( $store_name ) ?>
                            <br>
							<?php echo esc_html( $store_mobile ) ?>
                            <br>
							<?php echo esc_html( $store_address ) ?>
                        </p>
                    </div>
                </div>
                <div class="std-consignment">
                    <div class="tm_padd_15_20 tm_round_border">
                        <div class="tm_mb10 tm_invoice_head tm_align_center">
                            <div class="tm_invoice_left">
                                <p class="tm_mb3"><b class="tm_primary_color"><?php echo esc_html( 'For ' . $store_name ) ?></b></p>
                            </div>
                        </div>
                        <hr class="hr">
                        <div class="tm_invoice_head tm_mb10">
                            <div class="tm_invoice_left">
                                <h5 class="tm_mb2"><b class="tm_primary_color"> <?php echo esc_html( 'Invoice ID: #' . $insert_id ) ?></b></h5>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="tm_table tm_style1 tm_mb30">
                    <div class="tm_round_border">
                        <div class="tm_table_responsive">
                            <table>
                                <thead>
                                <tr>
                                    <th class="tm_width_3 tm_semi_bold tm_primary_color tm_gray_bg"><?php echo esc_html__( 'Item', 'kazi-cart' ); ?></th>
                                    <th class="tm_width_2 tm_semi_bold tm_primary_color tm_gray_bg"><?php echo esc_html__( 'Description', 'kazi-cart' ); ?></th>
                                    <th class="tm_width_3 tm_semi_bold tm_primary_color tm_gray_bg"><?php echo esc_html__( 'Price', 'kazi-cart' ); ?></th>
                                    <th class="tm_width_2 tm_semi_bold tm_primary_color tm_gray_bg"><?php echo esc_html__( 'Qty', 'kazi-cart' ); ?></th>
                                    <th class="tm_width_3 tm_semi_bold tm_primary_color tm_gray_bg"><?php echo esc_html__( 'Total', 'kazi-cart' ); ?></th>
                                </tr>
                                </thead>
                                <tbody>

								<?php

								$grand_total = [];

								foreach ( $products as $product_id => $quantity ) :
									$pro_name = get_the_title( $product_id );
									$price   = get_post_meta( $product_id, 'product_price', true );
									$total   = ( $price * $quantity );

									$grand_total[] = $total; ?>
                                    <tr>
                                        <td class="tm_width_3"> <?php echo esc_html( $pro_name ); ?>  </td>
                                        <td class="tm_width_4"> <?php echo ''; ?>  </td>
                                        <td class="tm_width_1"> <?php echo esc_html( $price ); ?> </td>
                                        <td class="tm_width_1"> <?php echo esc_html( $quantity ); ?></td>
                                        <td class="tm_width_3 tm_text_right">  <?php echo esc_html( $total ); ?></td>
                                    </tr>
								<?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="tm_invoice_footer std-payment-info">
                        <div class="tm_left_footer">
                            <p class="tm_mb2"><b class="tm_primary_color"><?php echo esc_html__( 'Payment info: ', 'kazi-cart' ) ?></b></p>
                            <p class="tm_m0"><?php echo esc_html__( 'Cash', 'kazi-cart' ); ?></p>
                        </div>
                        <div class="tm_right_footer">
                            <table>
                                <tbody>
                                <tr>
                                    <td class="tm_width_3 tm_primary_color tm_border_none tm_bold"><?php echo esc_html__( 'Subtotal', 'kazi-cart' ); ?></td>
                                    <td class="tm_width_3 tm_primary_color tm_text_right tm_border_none tm_bold">
										<?php echo esc_html( '&#2547;' . array_sum( $grand_total ) ); ?>
                                    </td>
                                </tr>
                                <tr>
                                    <td class="tm_width_3 tm_primary_color tm_border_none tm_pt0"><?php echo esc_html__( 'Shipping cost', 'kazi-cart' ) ?></td>
                                    <td class="tm_width_3 tm_primary_color tm_text_right tm_border_none tm_pt0"><?php echo esc_html__( '00', 'kazi-cart' ) . '&#2547;' ?></td>
                                </tr>
                                <tr>
                                    <td class="tm_width_3 tm_primary_color tm_border_none tm_pt0"><?php echo esc_html__( 'Tax', 'kazi-cart' ) ?> <span class="tm_ternary_color">(0%)</span></td>
                                    <td class="tm_width_3 tm_primary_color tm_text_right tm_border_none tm_pt0"><?php echo esc_html__( '00 &#2547;', 'kazi-cart' ) ?></td>
                                </tr>
								<?php ?>
                                <tr class="tm_border_top tm_border_bottom">
                                    <td class="tm_width_3 tm_border_top_0 tm_border_bottom tm_bold tm_f16 tm_primary_color"><?php echo esc_html__( 'Grand Total', 'kazi-cart' ) ?></td>
                                    <td class="tm_width_3 tm_border_top_0 tm_border_bottom tm_bold tm_f16 tm_primary_color tm_text_right"><?php echo esc_html( '&#2547;' . array_sum( $grand_total ) ); ?></td>
                                </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="tm_padd_15_20 tm_round_border">
                    <p class="tm_mb5"><b class="tm_primary_color"><?php echo esc_html__( 'Terms & Conditions: ', 'kazi-cart' ) ?> </b></p>
                    <p>
						<?php echo nl2br( esc_html( $store_term_condition ) ); ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
<!--</body>-->
<!--</html>-->
