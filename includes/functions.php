<?php

if ( ! defined( 'WPINC' ) ) { die; }
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
    
    
    add_action( 'show_user_profile', 'wc_w_wallet_money' );
    add_action( 'edit_user_profile', 'wc_w_wallet_money' );
    
    /**
     *
     * @param int $user
     */
    function wc_w_wallet_money( $user ) {
        ?>
  <h3><?php _e("WooCommerce Wallet", WC_WALLET_TEXT); ?></h3>
  <table class="form-table"> 
    <tr>
      <th><label for="wc_wallet"><?php _e("WC Wallet", WC_WALLET_TEXT); ?></label></th>
      <td>
        <input type="number" name="wc_wallet" id="wc_wallet" class="regular-text" 
            value="<?php echo esc_attr( get_user_meta( $user->ID, "wc_wallet", true ) ); ?>" /><br />
        <span class="description"><?php _e("The money in the wallet of this user."); ?></span>
    </td>
    </tr>
  </table>
<?php
}

add_action( 'personal_options_update', 'wc_wallet_field' );
add_action( 'edit_user_profile_update', 'wc_wallet_field' );

/**
 * 
 * @param int $user_id
 * @return boolean
 */
function wc_wallet_field( $user_id ) {
  $saved = false;
  if ( current_user_can( 'edit_user', $user_id ) ) {
  	$old = get_user_meta( $user_id, "wc_wallet", true );
  	$new = $_POST["wc_wallet"];
  	
  	update_user_meta( $user_id, "wc_wallet", $new );
    
  	$amount = $new - $old;
  	
  	if ( $new != $old ) {
  		wc_w_add_to_log( $user_id, $amount, 2, 0 );
  	}
    
    $saved = true;
  }
  return true;
}


/**
 * 
 * @param object $carts
 */
function woo_add_cart_fee( $carts ) {
	
	if ( is_checkout() || is_cart() || defined('WOOCOMMERCE_CHECKOUT') || defined('WOOCOMMERCE_CART') ) {
		if( is_user_logged_in() ){
			$amount = get_user_meta( get_current_user_id(), 'wc_wallet', true );
			if(isset($_POST['wc_w_field']) && $_POST['wc_w_field'] !== null && $_POST['wc_w_field'] != ""){
				$credit 	= $_POST['wc_w_field'];
				$on_hold = get_user_meta( get_current_user_id(), 'onhold_credits',true ) != 0 ? get_user_meta( get_current_user_id(), 'onhold_credits',true ) : 0;
				
				$cart_total = $carts->cart_contents_total;
				if( is_wallet_include_tax() ){
					$tax = this_get_tax( $carts );
					$cart_total = $cart_total + array_sum($tax);
				} 
				$in_wallet	= $amount;
				
				// Check if the amount is restricted
				if( is_wallet_restrict_max() ){
					// Check the restriction amount with entered amount
					if( get_wallet_restricted_amount() < $credit ){
							set_credit_in_cart( get_wallet_restricted_amount() );
							wc_add_notice( 'Credit is Restricted for the users to '.wc_price( get_wallet_restricted_amount() ).'.!', 'error' );
					}else{
						if( $credit <= $in_wallet ){
							if( $credit <= $cart_total ){
								if( set_credit_in_cart( $credit ) ){
									wc_add_notice( __('Credits added successfully!', WC_WALLET_TEXT) );
								}else{
									set_credit_in_cart( 0 );
									wc_add_notice( __('There is Error while adding credits!', WC_WALLET_TEXT), 'error' );
								}
							}else if( $credit > $cart_total ){
								if( set_credit_in_cart( $cart_total ) ){
									wc_add_notice( __('Credits adjusted with total!', WC_WALLET_TEXT) );
								}else{
									set_credit_in_cart( 0 );
									wc_add_notice( __('You dont\'t have sufficient credits in your account.', WC_WALLET_TEXT ) , 'error' );
								}
							}
						}else{
							set_credit_in_cart( 0 );
							wc_add_notice( __( 'You dont\'t have sufficient credits in your account.', WC_WALLET_TEXT ), 'error' );
						}
					}
				}else{
					if( $credit <= $in_wallet ){
						if( $credit <= $cart_total ){
							if( set_credit_in_cart( $credit ) ){
								wc_add_notice( __('Credits added successfully!', WC_WALLET_TEXT ) );
							}else{
								set_credit_in_cart( 0 );
								wc_add_notice( __('There is Error while adding credits!', WC_WALLET_TEXT ), 'error' );
							}
						}else if( $credit > $cart_total ){
							if( set_credit_in_cart( $cart_total ) ){
								wc_add_notice( __('Credits adjusted with total!', WC_WALLET_TEXT ) );
							}else{
								set_credit_in_cart( 0 );
								wc_add_notice( __('You dont\'t have sufficient credits in your account.', WC_WALLET_TEXT ) , 'error' );
							}
						}
					}else{
						set_credit_in_cart( 0 );
						wc_add_notice( 'You dont\'t have sufficient credits in your account.', 'error' );
					}
				}
					
				
			}else{
				$on_hold = get_user_meta( get_current_user_id(), 'onhold_credits',true ) != 0 ? get_user_meta( get_current_user_id(), 'onhold_credits',true ) : 0;
				
				$cart_total = $carts->cart_contents_total;
				if( is_wallet_include_tax() ){
					$tax = this_get_tax( $carts );
					$cart_total = $cart_total + array_sum($tax);
				}
				
				if( $on_hold > $amount  ){
					if( set_credit_in_cart( $amount ) ){
						wc_add_notice( __('You don\'t have sufficient credits!', WC_WALLET_TEXT) );
					}else{
						set_credit_in_cart( 0 );
						wc_add_notice( __('There is Error while adding credits!', WC_WALLET_TEXT), 'error' );
					}
				}
				
				if( is_wallet_restrict_max() ){
					// Check the restriction amount with entered amount
					if( $on_hold <= $amount  ){
						if( get_wallet_restricted_amount() < $on_hold ){
								set_credit_in_cart( get_wallet_restricted_amount() );
								wc_add_notice( __('Credit is Restricted for the users to ', WC_WALLET_TEXT).wc_price( get_wallet_restricted_amount() ).'!', 'error' );
						}else{
							if( $on_hold > $cart_total ){
								if( $amount >= $cart_total ){
									set_credit_in_cart( $cart_total );
									wc_add_notice( __('Credits adjusted with total', WC_WALLET_TEXT) );
								}else{
									set_credit_in_cart( 0 );
								}
							}
						}
					}
				}else{
					if( $on_hold > $cart_total ){
						if( $amount >= $cart_total ){
							set_credit_in_cart( $cart_total );
							wc_add_notice( __('Credits adjusted with total', WC_WALLET_TEXT) );
						}else{
							set_credit_in_cart( 0 );
						}
					}
				}
				
			}
		}
	}

	global $woocommerce; 
	if( is_user_logged_in() ){
		if( get_user_meta( get_current_user_id(), 'onhold_credits',true ) !== null && get_user_meta( get_current_user_id(), 'onhold_credits',true ) != 0 ){
			WC()->cart->add_fee( 'Credits', -get_user_meta( get_current_user_id(), 'onhold_credits',true ), true, '' );
		}
	}
	

}

add_action( 'woocommerce_cart_calculate_fees', 'woo_add_cart_fee' );

function is_wcw_show_in_cart () {
	$e = get_option( 'wcw_show_in_cart' );
	if( $e == 0 ){
		return true;
	}else{
		return false;
	}
}

function is_wcw_show_in_checkout() {
	$e = get_option( 'wcw_show_in_checkout' );
	if( $e == 0 ){
		return true;
	}else{
		return false;
	}
}


if ( is_wcw_show_in_cart() ) {
	add_action( 'woocommerce_cart_actions', 'wc_w_cart_hook' );
}


if ( is_wcw_show_in_checkout() ) {
	add_action( 'woocommerce_before_checkout_form', 'wc_w_cart_hook');
}

/**
 * 
 * @todo Add credits input to cart 
 */
function wc_w_cart_hook(){
	if( is_user_logged_in() ){

		$on_hold = get_user_meta( get_current_user_id(), 'onhold_credits',true ) != 0 ? get_user_meta( get_current_user_id(), 'onhold_credits',true ) : "";
		$amount = get_user_meta( get_current_user_id(), 'wc_wallet', true );
		$is_checkout	=	is_checkout();
		
		?>
		<?php if( $is_checkout ){ ?>
			<form method = "POST">
		<?php } ?>
		<style>
			.Credits{
				width: 100%;
    			text-align: left;
    			margin-top: 10px;
    			<?php if ( $is_checkout) { ?>
    			margin-bottom: 20px;
    			<?php } ?>
			}
			.credits-text{
				float: right;
			}
			<?php if ( $is_checkout ) { ?>
				.credits_amount {
					width: 200px;
				}
			<?php }	?>
		</style>
		<?php if ( $is_checkout ):  ?>
			<h3 id="order_review_heading"><?php _e( 'Pay with credits', 'woocommerce' ); ?></h3>
		<?php endif; ?>
		<div class = "Credits">
			<input type = "number" class = "input-text credits_amount" id = "coupon_code" name = "wc_w_field" placeholder = "<?php _e('Use Credits', WC_WALLET_TEXT); ?>" <?php if( is_wcw_is_float_value() ){ echo 'step="0.01"'; }?> value = "<?php echo $on_hold; ?>" min = "0" max = "<?php echo $amount; ?>">
			<input type="submit" class="button" name="add_credits" value="<?php _e('Add / Update credits', WC_WALLET_TEXT); ?>">
			<?php if( is_show_remaining_credits() ){ ?>
				<span class = "credits-text"><?php _e('Your Credits left is ', WC_WALLET_TEXT); ?><b><?php echo wc_price( $amount ); ?></b> <?php if( $on_hold != "" ){ echo "- ".wc_price($on_hold)." = <b>".wc_price($amount-$on_hold)."<b>"; }?></span>
			<?php }?>
		</div>
		<?php if( $is_checkout ){ ?>
			</form>
		<?php } ?>
	<?php 
	}else{
		echo '<div class = "Credits">';
		echo '<span>'.__('If you have credits, please login to add.', WC_WALLET_TEXT ).'</span>';
		echo '</div>';
	}
}

/**
 * 
 * @param int $amount
 * @return boolean
 */
function set_credit_in_cart( $amount ){
	if( update_user_meta( get_current_user_id(), 'onhold_credits', $amount ) ){
		return true;
	}else{
		return false;
	}
}

add_action('woocommerce_checkout_order_processed', 'wc_w_action_after_checkout');


/**
 * 
 * @param int $order_id
 */
function wc_w_action_after_checkout( $order_id ){
	if( is_user_logged_in() ){
		$uid = get_current_user_id();
		$onhold = get_user_meta( $uid, 'onhold_credits',true );
		$credit = get_user_meta( $uid, 'wc_wallet',true );
		if( $onhold != 0 ){
			update_user_meta( $uid, 'onhold_credits', 0 );
			update_user_meta( $uid, 'wc_wallet', $credit-$onhold );
			wc_w_add_to_log( $uid, $onhold, 0, $order_id );
		}
	}
}

add_action('wp_head', 'wc_m_after_calculate_totals');

/**
 * 
 * @todo Sets credits to 0 after checkout
 */
function wc_m_after_calculate_totals(){
	if ( WC()->cart->get_cart_contents_count() == 0 ) {
		set_credit_in_cart( 0 );
	}
}


/* =========== Insert Functions ============= */

/**
 * 
 * @param int $uid
 * @param int $amount
 * @param int $method
 * @subpackage 0 => Wallet to Credits
 * 			   1 => Credits to Wallet
 * @param int $order_id
 */
function wc_w_add_to_log( $uid, $amount, $method, $order_id ){
	/**
	 * 0 => Wallet to Credits
	 * 1 => Credits to Wallet
	 */
	switch( $method ){
		case 0: $method = 0; break;
		case 1: $method = 1; break;
		default: $method = 2; break;
	}
	$arg = array(
			"post_title"		=>	"Logs",
			"post_content"		=>	"",
			"post_type"			=>	"wcw_logs",
			"post_status"		=>	"publish"
	);
	$pid = wp_insert_post( $arg );
	add_post_meta( $pid, 'wcw_type', $method );
	add_post_meta( $pid, 'uid', $uid );
	add_post_meta( $pid, 'date', date('d M Y') );
	add_post_meta( $pid, 'oid', $order_id );
	add_post_meta( $pid, 'amount', $amount );
	
	if( is_wcw_notify_admin() ){
		$user_info = get_userdata( $uid );
		wcw_ntify_admin( get_option('admin_email'), $user_info->user_login, date('d M Y'), $order_id, $amount, wc_w_get_type( $method ) );
	}
}

add_action('wcw_after_changeto_cancel_order', 'action_on_cancel_order');

/**
 *
 * @param array $array
*/
function action_on_cancel_order( $array ){
	$order  = wc_get_order( $array['order_id'] );
	$es = $order->get_fees();
	foreach( $es as $key => $yl ){
		if( $yl['name'] == "Credits" ){
			$e = $key;
			break;
		}
	}
	$c = (string)$e;
	$amount = -1*$es[$c]['line_total'] + $order->get_total();
	$refunded = is_order_automatic_cancel() ? 1 : 0;
	$arg = array(
			"post_title"		=>	"WC Wallet",
			"post_content"		=>	"",
			"post_type"			=>	"wcw_corequest",
			"post_status"		=>	"publish"
	);
	$pid = wp_insert_post( $arg );
	add_post_meta( $pid, 'uid', $array['uid'] );
	add_post_meta( $pid, 'date', date('d M Y') );
	add_post_meta( $pid, 'oid', $array['order_id'] );
	add_post_meta( $pid, 'amount', $amount );
	/*
	 * 0 => If not refunded
	 * 1 => If refunded 
	 */
	add_post_meta( $pid, 'amount_refund', $refunded );

}

/* =========== Insert Functions Ends ============= */

/**
 * 
 * @param string $type
 */
function wc_w_get_type( $type ){
	switch( $type ){
		case 0: $txt = __("Wallet to Credits", WC_WALLET_TEXT); break;
		case 1: $txt = __("Credits to Wallet", WC_WALLET_TEXT); break;
		case 2: $txt = __("Changed By Admin", WC_WALLET_TEXT); break;
		default: $txt = __("Changed By Admin", WC_WALLET_TEXT); break;
	}
	
	echo $txt;
}



/**
 * 
 * @param object $_this
 * @return number
 */
function this_get_tax( $_this ){
	$cart = $_this->get_cart();
	$tax = $tax_rates = array();
	foreach ( $cart as $cart_item_key => $values ) {
		
		$_product = $values['data'];
	
		// Prices
		$base_price = $_product->get_price();
		$line_price = $_product->get_price() * $values['quantity'];
	
		// Tax data
		$taxes = array();
		$discounted_taxes = array();
	
		/**
		 * No tax to calculate
		*/
		if ( ! $_product->is_taxable() ) {
	
			// Discounted Price (price with any pre-tax discounts applied)
			$discounted_price      = 0;
			$line_subtotal_tax     = 0;
			$line_subtotal         = $line_price;
			$line_tax              = 0;
			$line_total            = WC_Tax::round( $discounted_price * $values['quantity'] );
	
			/**
			 * Prices include tax
			*/
		} elseif ( $_this->prices_include_tax ) {
	
			$base_tax_rates = $shop_tax_rates[ $_product->tax_class ];
			$item_tax_rates = $tax_rates[ $_product->get_tax_class() ];
	
			/**
			 * ADJUST TAX - Calculations when base tax is not equal to the item tax
			 *
			 * The woocommerce_adjust_non_base_location_prices filter can stop base taxes being taken off when dealing with out of base locations.
			 * e.g. If a product costs 10 including tax, all users will pay 10 regardless of location and taxes.
			 * This feature is experimental @since 2.4.7 and may change in the future. Use at your risk.
			*/
			if ( $item_tax_rates !== $base_tax_rates && apply_filters( 'woocommerce_adjust_non_base_location_prices', true ) ) {
	
				// Work out a new base price without the shop's base tax
				$taxes             = WC_Tax::calc_tax( $line_price, $base_tax_rates, true, true );
				
	
				// Now we have a new item price (excluding TAX)
				$line_subtotal     = round( $line_price - array_sum( $taxes ), WC_ROUNDING_PRECISION );
				$taxes             = WC_Tax::calc_tax( $line_subtotal, $item_tax_rates );
				$line_subtotal_tax = array_sum( $taxes );
	
				// Adjusted price (this is the price including the new tax rate)
				$adjusted_price    = ( $line_subtotal + $line_subtotal_tax ) / $values['quantity'];
	
				// Apply discounts
				$discounted_price  = $_this->get_discounted_price( $values, $adjusted_price, true );
				$discounted_taxes  = WC_Tax::calc_tax( $discounted_price * $values['quantity'], $item_tax_rates, true );
				$line_tax          = array_sum( $discounted_taxes );
				$line_total        = ( $discounted_price * $values['quantity'] ) - $line_tax;
	
				/**
				 * Regular tax calculation (customer inside base and the tax class is unmodified
				 */
			} else {
	
				// Work out a new base price without the item tax
				$taxes             = WC_Tax::calc_tax( $line_price, $item_tax_rates, true );
	
				// Now we have a new item price (excluding TAX)
				$line_subtotal     = $line_price - array_sum( $taxes );
				$line_subtotal_tax = array_sum( $taxes );
	
				// Calc prices and tax (discounted)
				$discounted_price = 0;
				$discounted_taxes = WC_Tax::calc_tax( $discounted_price * $values['quantity'], $item_tax_rates, true );
				$line_tax         = array_sum( $discounted_taxes );
				$line_total       = ( $discounted_price * $values['quantity'] ) - $line_tax;
			}
	
			// Tax rows - merge the totals we just got
			foreach ( array_keys( $_this->taxes + $discounted_taxes ) as $key ) {
				$tax[ $key ] = ( isset( $discounted_taxes[ $key ] ) ? $discounted_taxes[ $key ] : 0 ) + ( isset( $_this->taxes[ $key ] ) ? $_this->taxes[ $key ] : 0 );
			}
	
			/**
			 * Prices exclude tax
			 */
		} else {
			
			if( $_product->get_tax_class() != false ) {
				$item_tax_rates        = $tax_rates[ $_product->get_tax_class() ];
			} else {
				$item_tax_rates        = 0;
			}
	
			// Work out a new base price without the shop's base tax
			$taxes                 = WC_Tax::calc_tax( $line_price, $item_tax_rates );
	
			// Now we have the item price (excluding TAX)
			$line_subtotal         = $line_price;
			$line_subtotal_tax     = array_sum( $taxes );
	
			// Now calc product rates
			$discounted_price      = 0;
			$discounted_taxes      = WC_Tax::calc_tax( $discounted_price * $values['quantity'], $item_tax_rates );
			$discounted_tax_amount = array_sum( $discounted_taxes );
			$line_tax              = $discounted_tax_amount;
			$line_total            = $discounted_price * $values['quantity'];
	
			// Tax rows - merge the totals we just got
			foreach ( array_keys( $_this->taxes + $discounted_taxes ) as $key ) {
				try {
					$tax[ $key ] = ( isset( $discounted_taxes[ $key ] ) ? $discounted_taxes[ $key ] : 0 ) + ( isset( $_this->taxes[ $key ] ) ? $_this->taxes[ $key ] : 0 );
				} catch ( Exception $e ) {
					//No one cares
				}
			}
		}
		try {
			$tax 				= array_map( array( 'WC_Tax', 'round' ), $tax);
			$tax 				= array_sum($tax);
		} catch ( Exception $e ) {
			$tax	=	array();
		}
		$shipping_total	 	= WC()->shipping->shipping_total;
		$shipping_taxes		= WC()->shipping->shipping_taxes;
		if ( $_this->round_at_subtotal ) {
			$shipping_tax_total = WC_Tax::get_tax_total( $shipping_taxes );
			$shipping_taxes     = array_map( array( 'WC_Tax', 'round' ), $shipping_taxes );
		} else {
			$shipping_tax_total = array_sum( $shipping_taxes );
		}
		$array 				= array(
				'tax'			=>	$tax,
				'shipping'		=>	$shipping_total,
				'shipping_tax'	=> 	$shipping_tax_total
		);
	}
	return $array;
}

function wcw_get_new_user_discount_price() {
	$amount = get_option( "wcw_new_user_credits" );
	if ( $amount != "" ) {
		return $amount;
	} else {
		return 0;
	}
}

/* All IS functions starts */

function is_wcw_is_float_value(){
	return get_option( "wcw_is_float_value" );
}

/**
 * 
 * @return boolean
 */
function is_cancel_request_enabled(){
	$e = get_option( 'wcw_cancel_req' );
	if( $e == 1 ){
		return true;
	}else{
		return false;
	}
}

/**
 * 
 * @return boolean
 */
function is_wallet_include_tax(){
	$e = get_option('wcw_apply_tax');
	if( $e == 1 ){
		return true;
	}else{
		return false;
	}
}

/**
 * 
 * @return boolean
 */
function is_wallet_restrict_max(){
	$e = get_option('wcw_restrict_max');
	if( $e != "" || $e != null || $e != 0 ){
		return true;
	}else{
		return false;
	}
}

/**
 * 
 * @return boolean
 */
function is_show_remaining_credits(){
	$e = get_option('wcw_remining_credits');
	if( $e == 1 ){
		return true;
	}else{
		return false;
	}
}

/**
 * 
 * @return boolean
 */
function is_order_automatic_cancel(){
	$e = get_option('wcw_automatic_cancel_req');
	if( $e == 1 ){
		return true;
	}else{
		return false;
	}
}


/**
 * 
 * @return boolean
 */
function is_wcw_notify_admin(){
	$e = get_option('wcw_notify_admin');
	if( $e == 1 ){
		return true;
	}else{
		return false;
	}
}

function is_wcw_show_in_myaccount() {
	$e = get_option( 'wcw_show_in_myaccount' );
	if( $e == 1 ){
		return true;
	}else{
		return false;
	}
}

function is_wcw_remove_cancel_logs() {
	$e = get_option( 'wcw_remove_cancel_logs' );
	if( $e == 1 ){
		return true;
	}else{
		return false;
	}
}


/* All IS functions ends */

/* Get functions */

function get_wallet_restricted_amount(){
	return (int)get_option('wcw_restrict_max');
}

/**
 *
 * @return array
 */
function wc_w_get_log(){
	$args = array(
			"posts_per_page"	=>	-1,
			'post_type'        	=> 	'wcw_logs'
	);
	$posts = get_posts( $args );
	$items = array();
	$i = 0;
	foreach( $posts as $postt ){
		$pid = $postt->ID;
		$items[$i]['oid']		=	get_post_meta( $pid, 'oid', true );
		$items[$i]['uid']		=	get_post_meta( $pid, 'uid', true );
		$items[$i]['amount'] 	=	get_post_meta( $pid, 'amount', true );
		$items[$i]['date']		=	get_post_meta( $pid, 'date', true );
		$items[$i]['wcw_type']  = 	get_post_meta( $pid, 'wcw_type', true );
		$items[$i]['ID']		=	$pid;
		$i++;
	}
	
	return $items;
}

/**
 *
 * @return array
 */
function wc_w_get_cancel_requests(){
	$args = array(
			"posts_per_page"	=>	-1,
			'post_type'        	=> 	'wcw_corequest',
			"orderby"			=>	'date'
	);
	$posts = get_posts( $args );
	
	$i = 0;
	if( count( $posts ) != 0 ){
		foreach( $posts as $post ){
			$pid = $post->ID;
	
			$items[$i]['oid']		=	get_post_meta( $pid, 'oid', true );
			$items[$i]['uid']		=	get_post_meta( $pid, 'uid', true );
			$items[$i]['amount'] 	=	get_post_meta( $pid, 'amount', true );
			$items[$i]['date']		=	get_post_meta( $pid, 'date', true );
			$items[$i]['refund']	=	get_post_meta( $pid, 'amount_refund', true);
			$items[$i]['ID']		=	$pid;
			$i++;
		}
	}
	if( $i == 0 ){
		return 0;
	}else{
		return $items;
	}
	
}

/**
 * 
 * @return array $items
 */
function get_the_order_in_log(){
	$args = array(
			"posts_per_page"	=>	-1,
			'post_type'        	=> 	'wcw_corequest'
	);
	$posts = get_posts( $args );
	$items = array();
	$i = 0;
	foreach( $posts as $postt ){
		$pid = $postt->ID;
		if( get_post_meta( $pid, 'amount_refund', true ) == 1 ){
			$items[] = $pid;
			$i++;
		}
		
	}
	
	return $items;
}

/**
 * 
 * @return array
 */
function get_wcw_only_methods(){
	$array = explode( ",",get_option('wcw_payment_method') );
	return $array;
}

/* Get functions */


/**
 * 
 * @param string $msg
 * @param string $status
 */
function wcw_plugin_success_msg( $msg, $status = "success" ){
	if( $status == "success" ){
		echo '<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible"> 
			<p><strong>'.$msg.'</strong></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
	}else{
		echo '<div class="error"><p>'.__($msg, WC_WALLET_TEXT).'</p></div>';
	}
}

/**
 * 
 * @param int $post
 * @return boolean
 */
function wcw_update_form( $post ){
	if( isset( $post['wcw_payment_method'] ) ){
		$str = implode( ",", $post['wcw_payment_method'] );
		update_option('wcw_payment_method', $str);
	}else{
		update_option('wcw_payment_method', '');
	}
	
	if( isset( $post['wcw_apply_tax'] ) ){
		update_option('wcw_apply_tax', $post['wcw_apply_tax']);
	}
	
	if( isset( $post['wcw_is_float_value'] ) ){
		update_option('wcw_is_float_value', $post['wcw_is_float_value']);
	}
	
	if( isset( $post['wcw_transfer_only'] ) ){
		update_option('wcw_transfer_only', json_encode( $post['wcw_transfer_only'] ) );
	}else{
		update_option('wcw_transfer_only', "" );
	}
		
	wcw_yes_or_no_update( $post, 'wcw_restrict_max' );
	wcw_yes_or_no_update( $post, 'wcw_new_user_credits' );
	wcw_yes_or_no_update( $post, 'wcw_notify_admin' );
	wcw_yes_or_no_update( $post, 'wcw_remining_credits' );
	wcw_yes_or_no_update( $post, 'wcw_cancel_req' );
	wcw_yes_or_no_update( $post, 'wcw_automatic_cancel_req' );
	wcw_yes_or_no_update( $post, 'wcw_notify_on_cancel_req' );
	wcw_yes_or_no_update( $post, 'wcw_show_in_myaccount' );
	wcw_yes_or_no_update( $post, 'wcw_show_in_cart' );
	wcw_yes_or_no_update( $post, 'wcw_show_in_checkout' );
	wcw_yes_or_no_update( $post, 'wcw_remove_cancel_logs' );
	
	return true;
}

/**
 * 
 * @param array $post
 * @param string $str
 */
function wcw_yes_or_no_update( $post, $str ){
	if( isset( $post[$str] ) ){
		update_option($str, $post[$str]);
	}else{
		update_option($str, '');
	}
}

/* Cancel Order Starts from Here */

/**
 * 
 * @return number
 */
function get_count_cancel_request(){
	$args = array(
			"posts_per_page"	=>	-1,
			'post_type'        	=> 	'wcw_corequest',
			"orderby"			=>	'date'
	);
	$posts = get_posts( $args );
	
	$i = 0;
	if( count( $posts ) != 0 ){
		foreach( $posts as $post ){
			$pid = $post->ID;
			$i	=	$i + get_post_meta( $pid, 'amount_refund', true);
		}
	}
	
	return count( $posts )-$i;
}
	
if( is_cancel_request_enabled() ){
    
	add_filter('woocommerce_my_account_my_orders_actions', 'add_wc_cancel_my_account_orders_status', 100, 2);
	/**
	 * 
	 * @param unknown $actions
	 * @param unknown $order
	 * @return multitype:string Ambigous <string, mixed>
	 */
	function add_wc_cancel_my_account_orders_status( $actions, $order )    {
		$order =  json_decode( $order ); 
		$check	=	wcw_check_the_order_status( $order->id, "wc-".$order->status );
	
		if ( $check == 1 ) {
			$actions['cancelled'] = array(
					'url' 		=> wp_nonce_url(admin_url('admin-ajax.php?action=request_for_cancell_wcw&order_id=' . $order->id), 'mark_order_as_cancell_request'), 
					'name' 		=> 'Send Cancel Request', 
					'action' 	=> "mark_order_as_cancell_request"
			);
		} else if ( $check == 2 ) {
			$actions['cancelled'] = array(
					'url' 		=> '#',
					'name' 		=> 'Cancel request sent',
					'action' 	=> "mark_order_as_cancell_request"
			);
		}
	
		return $actions;
	}
	
	add_action('wp_ajax_request_for_cancell_wcw', 'mark_order_as_cancell_request');
	add_action('wp_ajax_nopriv_request_for_cancell_wcw', 'mark_order_as_cancell_request');
	
	/**
	 * 
	 * @todo Ajax to send cancel request
	 */
	function mark_order_as_cancell_request()    {
	
		if( is_user_logged_in() ){
			$order_id = (int)$_GET['order_id'] ? (int)$_GET['order_id'] : 0;
		}else{
			wp_safe_redirect(wp_get_referer() ? wp_get_referer() :
				get_permalink(get_option('woocommerce_myaccount_page_id')));
			die();
		}
		
		if( $order_id != 0 ){
			$order = wc_get_order($order_id);
			do_action( 'wcw_before_changeto_cancel_order', array( 'order_id' => $order_id, 'uid' => get_current_user_id() ) );
			if( is_order_automatic_cancel() ){
				$order->update_status('cancelled');
			}else{
				$order->update_status('wc-cancel-request');
			}
			do_action( 'wcw_after_changeto_cancel_order', array( 'order_id' => $order_id, 'uid' => get_current_user_id() ) );
		}
		wp_safe_redirect(wp_get_referer() ? wp_get_referer() :
				get_permalink(get_option('woocommerce_myaccount_page_id')));
		die();
		
	}
	
	/**
	 * 
	 * @todo Registed a order status
	 */
	function wpex_wc_register_post_statuses() {
		register_post_status( 'wc-cancel-request', array(
				'label'                     => _x( 'On Cancel Request', 'WooCommerce Order status', 'wc_wallet' ),
				'public'                    => true,
				'exclude_from_search'       => false,
				'show_in_admin_all_list'    => true,
				'show_in_admin_status_list' => true,
				'label_count'               => _n_noop( 'On Cancel Request <span class="count">(%s)</span>', 'On Cancel Request <span class="count">(%s)</span>', 'wc_wallet' )
		) );
	}
	add_filter( 'init', 'wpex_wc_register_post_statuses' );
	
	/**
	 * 
	 * @param array $order_statuses
	 * @return array
	 */
	function wpex_wc_add_order_statuses( $order_statuses ) {
		$order_statuses['wc-cancel-request'] = _x( 'On Cancel Request', 'WooCommerce Order status', 'wc_wallet' );
		return $order_statuses;
	}
	add_filter( 'wc_order_statuses', 'wpex_wc_add_order_statuses' );
	
	
	add_action('wp_ajax_wcw_refund_order', 'wcw_refund_order');
	add_action('wp_ajax_nopriv_wcw_refund_order', 'wcw_refund_order');
	function wcw_refund_order(){
		if( is_admin() ){
			$order_id 	=	isset( $_GET['order_id'] ) ? (int)$_GET['order_id'] : false;
			$pid 		=	isset( $_GET['pid'] ) ? (int)$_GET['pid'] : false;
			
			if( $order_id != 0 && $order_id != false && $pid != false ){
				$order = wc_get_order( $order_id );
				if( $order !== null ){
					$order->update_status("cancelled");
					update_post_meta( $pid, 'amount_refund', 1 );
				}
			}
		}
		wp_safe_redirect(wp_get_referer() ? wp_get_referer() : admin_url("admin.php?page=wc-wallet-cancel-requests"));
		exit;
	}
}

/**
 * 
 * @param string $email
 * @param string $uname
 * @param DateInterval $time
 * @param int $oid
 * @param int $amount
 * @param string $type
 */
function wcw_ntify_admin( $email, $uname, $time, $oid, $amount, $type ){
	$message = "Dear Admin, $uname has moved $type of amount $amount for the order #$oid at the time $time";
	wp_mail( $email, "Changes in credits", $message );
}

add_shortcode('wc_wallet_show_balance', 'wc_wallet_show_balance');

function wc_wallet_show_balance(){
	if( is_user_logged_in() ){
		$text	=	apply_filters( "wcw_available_credits_text", "Available Credits: " );
		echo __( $text , WC_WALLET_TEXT ).wc_price(get_user_meta( get_current_user_id(), "wc_wallet", true ));
	}else{
		_e("You need to login to see your credits", WC_WALLET_TEXT);
	}
}


/**
 * 
 * @param int $order_type
 * @param string $old_status
 * 
 * @since 1.0.2
 * @property 1.0.4 accepts credidts again which is used already
 * 
 */
function wcw_check_the_order_status( $order_id, $old_status = '' ){
	$order 		= 	new WC_Order( $order_id );
	$order_type	=	get_post_meta( $order_id, '_payment_method', true );
	$order_status	=	$old_status;
	$array	=	json_decode( get_option('wcw_transfer_only'), true );
	$order_total = get_post_meta($order_id, '_order_total', true);
	$order_array = isset($array[$order_type])	?	$array[$order_type]	:	false;
	
	$args = array(
			'post_status' 		=> 'publish',
			'posts_per_page' 	=> -1,
			'post_type'			=>	'wcw_corequest',
			'meta_query'    	=> array(
					array(
							'key'       => 'oid',
							'value'     => $order_id,
							'compare'   => '='
					)
			)
	);
	
	
	$corequests	=	get_posts( $args );
	
	$is_already_refund_sent	=	count( $corequests )	==	0	?	true	:	false;
	
	if( ( $order_array && array_search( $order_status,  $order_array ) !== false ) || $order_total != "0.00" ){
		if ( $is_already_refund_sent ) {
			return 1;
		} else {
			return 2;
		}
	}else{
		return false;
	}
}


add_action( 'user_register', 'wcw_new_user_credits', 10, 1 );

/**
 * 
 * @param int $user_id
 * @todo Add offer money for new users
 */
function wcw_new_user_credits( $user_id ) {
	update_user_meta ( $user_id, 'wc_wallet', wcw_get_new_user_discount_price() );
}

/* ========= My Account page =========== */

if ( is_wcw_show_in_myaccount() ) {
	
	function wcw_custom_endpoints() {
	    add_rewrite_endpoint( 'wallet', EP_ROOT | EP_PAGES );
	}
	
	add_action( 'init', 'wcw_custom_endpoints' );
	
	/**
	 * 
	 * @param array $vars
	 */
	function wcw_custom_query_vars( $vars ) {
	    $vars[] = 'wallet';
	
	    return $vars;
	}
	
	add_filter( 'query_vars', 'wcw_custom_query_vars', 0 );
	
	function wcw_custom_flush_rewrite_rules() {
	    flush_rewrite_rules();
	}
	
	add_action( 'wp_loaded', 'wcw_custom_flush_rewrite_rules' );
		
	if ( !function_exists( "wcw_add_wallet_in_myaccount" ) ) {
		/**
		 * 
		 * @param array $items
		 */
		function wcw_add_wallet_in_myaccount ( $items ) {
			$logout = $items['customer-logout'];
			unset( $items['customer-logout'] );

			// Allows users to change the tab name
			$default_lable	=	apply_filters( 'wcw_wallet_myaccount_name', 'Wallets' );
			
			$label	=	__( $default_lable, WC_WALLET_TEXT );
			
			$items["wallet"]	=	$label;
			
			$items	=	apply_filters( "wcw_wallet_myaccount_tab", $items );
			
			$items['customer-logout'] = $logout;
			
			return $items;
		}
		
		add_filter( 'woocommerce_account_menu_items', 'wcw_add_wallet_in_myaccount' );
	}
	
	function wcw_myaccount_endpoint() {
		$user_id	=	get_current_user_id();
		?>
		<p><?php _e( 'Hello' ); ?> <b><?php echo get_user_meta( $user_id, "nickname", true ); ?></b><br>
		<?php _e( 'Your wallet balance is', WC_WALLET_TEXT ); ?> <b><?php echo wc_price( get_user_meta( $user_id , 'wc_wallet', true ) ); ?></b></p>
		
		<table style="width:100%" class = "wc-wallet-myaccount-table">
			<tr>
				<td><?php _e( 'Transaction Number', WC_WALLET_TEXT ); ?></td>
				<td><?php _e( 'Order Number', WC_WALLET_TEXT ); ?></td>
				<td><?php _e( 'Date', WC_WALLET_TEXT ); ?></td>
				<td><?php _e( 'Usage', WC_WALLET_TEXT ); ?></td>
				<td><?php _e( 'Amount', WC_WALLET_TEXT ); ?></td>
			</tr>
			
			<?php 
			$args = array(
					"posts_per_page"	=>	-1,
					'post_type'        	=> 	'wcw_logs',
					'meta_query'		=>	array(
							array(
									'key'       => 'uid',
									'value'     => $user_id,
									'compare'   => '='
							)
					)
			);
			$posts = get_posts( $args );
			foreach( $posts as $postt ){
				echo "<tr>";
				$pid = $postt->ID;
				echo  "<td>".$pid."</td>";
				echo  "<td>".get_post_meta( $pid, 'oid', true )."</td>";
				echo  "<td>".get_post_meta( $pid, 'date', true )."</rd>";
				switch( get_post_meta( $pid, 'wcw_type', true ) ){
					case 0: $method = __( "Credits/money used from wallet", WC_WALLET_TEXT ); break;
					case 1: $method = __( "Credits/money added to wallet", WC_WALLET_TEXT ); break;
					case 2: $method = __( "Changed by admin", WC_WALLET_TEXT ); break;
					default: $method = __( "Changed by admin", WC_WALLET_TEXT ); break;
				}
				echo  "<td>".$method."</td>";
				echo  "<td>".wc_price ( get_post_meta( $pid, 'amount', true ) )."</td>";
				echo "</tr>";
			}
			?>
		</table>
		
		<?php  
	}
	
	add_action( 'woocommerce_account_wallet_endpoint', 'wcw_myaccount_endpoint' );
}

/* ========= My Account page ends =========== */


/* ========= Dashboard Widget Starts =========== */

/**
 * Add a widget to the dashboard.
 *
 * This function is hooked into the 'wp_dashboard_setup' action below.
 */
function wcw_add_dashboard_widgets() {

	wp_add_dashboard_widget(
			'wcw_dashboard_widget',         // Widget slug.
			__( 'Wallet Balance', WC_WALLET_TEXT ),         // Title.
			'wcw_dashboard_widget_function' // Display function.
			);
}
add_action( 'wp_dashboard_setup', 'wcw_add_dashboard_widgets' );

/**
 * Create the function to output the contents of our Dashboard Widget.
 */
function wcw_dashboard_widget_function() {
	echo "<h1>".wc_price( get_user_meta( get_current_user_id() , 'wc_wallet', true ) )."</h1>";
}

/* ========= Dashboard Widget ends =========== */


/* ========= AJAX log actions ========= */

add_action( "wp_ajax_delete_credit_logs", "wcw_delete_credit_logs" );
/**
 * 
 * Deletes the posts ids of from the arugment
 */
function wcw_delete_credit_logs () {
	$ids	=	isset( $_POST["wcw_ids"] )	?	$_POST["wcw_ids"]	:	false;
	
	$status = false;
	if ( $ids && count( $ids ) != 0 ) {
		try {
			foreach ( $ids as $id ) {
				wp_delete_post( $id );
			}
			$status = true;
		} catch ( Exception $e ) {
			
		}
	}
	
	$res	=	array( "status" => $status);
	echo json_encode( $res );
	
	wp_die();
}

/* ========= AJAX log actions ========= */

}
?>