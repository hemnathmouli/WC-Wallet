<?php

if ( ! defined( 'WPINC' ) ) { die; }
if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	
	
add_action( 'show_user_profile', 'wc_w_wallet_money' );
add_action( 'edit_user_profile', 'wc_w_wallet_money' );
function wc_w_wallet_money( $user ) {
	?>
  <h3><?php _e("WooCommerce Wallet", "blank"); ?></h3>
  <table class="form-table"> 
    <tr>
      <th><label for="wc_wallet"><?php _e("WC Wallet"); ?></label></th>
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
    update_user_meta( $user_id, "wc_wallet", $_POST["wc_wallet"] );
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
					$cart_total = $cart_total + $tax;
				} 
				$in_wallet	= $amount;
				
				// Check if the amount is restricted
				if( is_wallet_restrict_max() ){
					// Check the restriction amount with entered amount
					if( get_wallet_restricted_amount() < $cart_total ){
						// Add restricted amount if the amount is larger than the restriction
						if( set_credit_in_cart( get_wallet_restricted_amount() ) ){
							wc_add_notice( 'Credit is Restricted for the users to '.wc_price( get_wallet_restricted_amount() ).'.!');
						}else{
							set_credit_in_cart( 0 );
							wc_add_notice( 'There is Error while adding credits.!', 'error' );
						}
					}else{
						if( $credit <= $in_wallet ){
							if( $credit <= $cart_total ){
								if( set_credit_in_cart( $credit ) ){
									wc_add_notice( 'Credits added sucessfully.!');
								}else{
									set_credit_in_cart( 0 );
									wc_add_notice( 'There is Error while adding credits.!', 'error' );
								}
							}else if( $credit > $cart_total ){
								if( set_credit_in_cart( $cart_total ) ){
									wc_add_notice( 'Credits adjusted with total.!');
								}else{
									set_credit_in_cart( 0 );
									wc_add_notice( 'You dont\'t have sufficient credits in your account.', 'error' );
								}
							}
						}else{
							set_credit_in_cart( 0 );
							wc_add_notice( 'You dont\'t have sufficient credits in your account.', 'error' );
						}
					}
				}else{
					if( $credit <= $in_wallet ){
						if( $credit <= $cart_total ){
							if( set_credit_in_cart( $credit ) ){
								wc_add_notice( 'Credits added sucessfully.!');
							}else{
								set_credit_in_cart( 0 );
								wc_add_notice( 'There is Error while adding credits.!', 'error' );
							}
						}else if( $credit > $cart_total ){
							if( set_credit_in_cart( $cart_total ) ){
								wc_add_notice( 'Credits adjusted with total.!');
							}else{
								set_credit_in_cart( 0 );
								wc_add_notice( 'You dont\'t have sufficient credits in your account.', 'error' );
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
					$cart_total = $cart_total + $tax;
				}
				
				if( is_wallet_restrict_max() ){
					// Check the restriction amount with entered amount
					if( get_wallet_restricted_amount() < $on_hold ){
						if( set_credit_in_cart( get_wallet_restricted_amount() ) ){
							wc_add_notice( 'Credit is Restricted for the users to '.wc_price( get_wallet_restricted_amount() ).'.!');
						}else{
							set_credit_in_cart( 0 );
							wc_add_notice( 'There is Error while adding credits.!', 'error' );
						}
					}else{
						if( $on_hold > $cart_total ){
							if( $amount >= $cart_total ){
								set_credit_in_cart( $cart_total );
								wc_add_notice( 'Credits adjusted with total.!' );
							}else{
								set_credit_in_cart( 0 );
							}
						}
					}
				}else{
					if( $on_hold > $cart_total ){
						if( $amount >= $cart_total ){
							set_credit_in_cart( $cart_total );
							wc_add_notice( 'Credits adjusted with total.!' );
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
			WC()->cart->add_fee( 'Credits', -get_user_meta( get_current_user_id(), 'onhold_credits',true ), false, '' );
		}
	}
	

}
add_action( 'woocommerce_cart_calculate_fees', 'woo_add_cart_fee' );

add_action( 'woocommerce_cart_actions', 'wc_w_cart_hook' );
function wc_w_cart_hook(){
	if( is_user_logged_in() ){

		$on_hold = get_user_meta( get_current_user_id(), 'onhold_credits',true ) != 0 ? get_user_meta( get_current_user_id(), 'onhold_credits',true ) : "";
		$amount = get_user_meta( get_current_user_id(), 'wc_wallet', true );
		
		?>
		<style>
			.Credits{
				width: 100%;
    			text-align: left;
    			margin-top: 10px;
			}
			.credits-text{
				float: right;
			}
		</style>
		<div class = "Credits">
			<input type = "number" class = "input-text credits_amount" id = "coupon_code" name = "wc_w_field" placeholder = "Use Credits" value = "<?php echo $on_hold; ?>" min = "0" max = "<?php echo $amount; ?>">
			<input type="submit" class="button" name="add_credits" value="Add / Update Credits">
			<?php if( is_show_remaining_credits() ){ ?>
				<span class = "credits-text">Your Credits left is <b><?php echo wc_price( $amount ); ?></b> <?php if( $on_hold != "" ){ echo "- ".wc_price($on_hold)." = <b>".wc_price($amount-$on_hold)."<b>"; }?></span>
			<?php }?>
		</div>
		
	<?php 
	}else{
		echo '<div class = "Credits">';
		echo '<span>If you have credits, please login to add.</span>';
		echo '</div>';
	}
}

function set_credit_in_cart( $amount ){
	if( update_user_meta( get_current_user_id(), 'onhold_credits', $amount ) ){
		return true;
	}else{
		return true;
	}
}

add_action('woocommerce_checkout_order_processed', 'wc_w_action_after_checkout');

function wc_w_action_after_checkout( $order_id ){
	if( is_user_logged_in() ){
		$uid = get_current_user_id();
		$onhold = get_user_meta( $uid, 'onhold_credits',true );
		$credit = get_user_meta( $uid, 'wc_wallet',true );
		if( $onhold !== null ){
			update_user_meta( $uid, 'onhold_credits', 0 );
			update_user_meta( $uid, 'wc_wallet', $credit-$onhold );
		}
	}
	wc_w_add_to_log( $uid, $onhold, 0, $order_id );
}

add_action('wp_head', 'wc_m_after_calculate_totals');
add_action( 'rf_get_the_cart', 'wc_m_after_calculate_totals' );

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
		default: $method = 0; break;
	}
	$arg=array(
			"post_title"		=>	"",
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

}

/* =========== Insert Functions Ends ============= */

/**
 * 
 * @param string $type
 */
function wc_w_get_type( $type ){
	switch( $type ){
		case 0: $txt = "Wallet to Credits"; break;
		case 1: $txt = "Credits to Wallet"; break;
		default: $txt = ""; break;
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
	$tax = array();
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
			$discounted_price      = $_this->get_discounted_price( $values, $base_price, true );
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
				$discounted_price = $_this->get_discounted_price( $values, $base_price, true );
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
	
			$item_tax_rates        = $tax_rates[ $_product->get_tax_class() ];
	
			// Work out a new base price without the shop's base tax
			$taxes                 = WC_Tax::calc_tax( $line_price, $item_tax_rates );
	
			// Now we have the item price (excluding TAX)
			$line_subtotal         = $line_price;
			$line_subtotal_tax     = array_sum( $taxes );
	
			// Now calc product rates
			$discounted_price      = $_this->get_discounted_price( $values, $base_price, true );
			$discounted_taxes      = WC_Tax::calc_tax( $discounted_price * $values['quantity'], $item_tax_rates );
			$discounted_tax_amount = array_sum( $discounted_taxes );
			$line_tax              = $discounted_tax_amount;
			$line_total            = $discounted_price * $values['quantity'];
	
			// Tax rows - merge the totals we just got
			foreach ( array_keys( $_this->taxes + $discounted_taxes ) as $key ) {
				$tax[ $key ] = ( isset( $discounted_taxes[ $key ] ) ? $discounted_taxes[ $key ] : 0 ) + ( isset( $_this->taxes[ $key ] ) ? $_this->taxes[ $key ] : 0 );
			}
		}
		$tax = array_map( array( 'WC_Tax', 'round' ), $tax);
	}
	return array_sum($tax);
}

/* All IS functions starts */

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
			'post_type'        	=> 	'wcw_logs',
			"orderby"			=>	'date'
	);
	$posts = get_posts( $args );

	foreach( $posts as $post ){
		$pid = $post->ID;

		$items[$i]['oid']		=	get_post_meta( $pid, 'oid', true );
		$items[$i]['uid']		=	get_post_meta( $pid, 'uid', true );
		$items[$i]['amount'] 	=	get_post_meta( $pid, 'amount', true );
		$items[$i]['date']		=	get_post_meta( $pid, 'date', true );
		$items[$i]['ID']		=	$pid;

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

/* Get functions */

function wcw_plugin_success_msg( $msg, $status = "success" ){
	if( $status == "success" ){
		echo '<div id="setting-error-settings_updated" class="updated settings-error notice is-dismissible"> 
			<p><strong>'.$msg.'</strong></p><button type="button" class="notice-dismiss"><span class="screen-reader-text">Dismiss this notice.</span></button></div>';
	}else{
		echo '<div class="error"><p>'.$msg.'</p></div>';
	}
}

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
		
	wcw_yes_or_no_update( $post, 'wcw_restrict_max' );
	wcw_yes_or_no_update( $post, 'wcw_notify_admin' );
	wcw_yes_or_no_update( $post, 'wcw_remining_credits' );
	wcw_yes_or_no_update( $post, 'wcw_cancel_req' );
	wcw_yes_or_no_update( $post, 'wcw_automatic_cancel_req' );
	wcw_yes_or_no_update( $post, 'wcw_notify_on_cancel_req' );
	
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
if( is_cancel_request_enabled() ){
	add_filter('woocommerce_my_account_my_orders_actions', 'add_wc_cancel_my_account_orders_status', 100, 2);
	function add_wc_cancel_my_account_orders_status( $actions, $order )    {
	
		if ($order->id) {
			$the_order = wc_get_order($order->id);
		}
	
	
		if ($the_order->has_status(array('on-hold', 'pending', 'processing'))) {
			$actions['cancelled'] = array(
					'url' 		=> wp_nonce_url(admin_url('admin-ajax.php?action=request_for_cancell_wcw&order_id=' . $order->id), 'mark_order_as_cancell_request'), 
					'name' 		=> 'Send Cancel Request', 
					'action' 	=> "mark_order_as_cancell_request"
			);
		}
	
		return $actions;
	}
	
	add_action('wp_ajax_request_for_cancell_wcw', 'mark_order_as_cancell_request');
	add_action('wp_ajax_nopriv_request_for_cancell_wcw', 'mark_order_as_cancell_request');
	function mark_order_as_cancell_request()    {
	
		if( is_user_logged_in() ){
			$order_id = (int)$_GET['order_id'] ? (int)$_GET['order_id'] : 0;
		}else{
			home_url();
			die();
		}
		
		if( $order_id != 0 ){
			$order = wc_get_order($order_id);
			do_action( 'wcw_before_changeto_cancel_order', array( 'order_id' => $order_id, 'uid' => get_current_user_id() ) );
			$order->update_status('wc-cancel-request');
			do_action( 'wcw_after_changeto_cancel_order', array( 'order_id' => $order_id, 'uid' => get_current_user_id() ) );
		}
		wp_safe_redirect(wp_get_referer() ? wp_get_referer() :
				get_permalink(get_option('woocommerce_myaccount_page_id')));
		die();
		
	}
	
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
	
	// Add New Order Statuses to WooCommerce
	function wpex_wc_add_order_statuses( $order_statuses ) {
		$order_statuses['wc-cancel-request'] = _x( 'On Cancel Request', 'WooCommerce Order status', 'wc_wallet' );
		return $order_statuses;
	}
	add_filter( 'wc_order_statuses', 'wpex_wc_add_order_statuses' );
}


}
?>