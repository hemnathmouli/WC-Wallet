<?php
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
function wc_wallet_field( $user_id ) {
  $saved = false;
  if ( current_user_can( 'edit_user', $user_id ) ) {
    update_user_meta( $user_id, "wc_wallet", $_POST["wc_wallet"] );
    $saved = true;
  }
  return true;
}

function woo_add_cart_fee() {

	global $woocommerce; 
	if( is_user_logged_in() ){
		if( get_user_meta( get_current_user_id(), 'onhold_credits',true ) !== null && get_user_meta( get_current_user_id(), 'onhold_credits',true ) != 0 ){
			WC()->cart->add_fee( 'Credits :', -get_user_meta( get_current_user_id(), 'onhold_credits',true ), false, '' );
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
		<div class = "Credits">
			<input type = "number" class = "input-text" id = "coupon_code" name = "wc_w_field" placeholder = "Use Credits" value = "<?php echo $on_hold; ?>" min = "0" max = "<?php echo $amount; ?>">
			<input type="submit" class="button" name="add_credits" value="Add / Update Credits"><span class = "credits-text">Your Credits left is <b><?php echo wc_price( $amount ); ?></b> <?php if( $on_hold != "" ){ echo "- ".wc_price($on_hold)." = <b>".wc_price($amount-$on_hold)."<b>"; }?></span>
			<input type = "hidden" name = "Wc_total" value = "<?php echo decbin( WC()->cart->total + $on_hold ); ?>">
		</div>
		
	<?php 
	}else{
		echo '<div class = "Credits">';
		echo '<span>If you have credits, please login to addit.</span>';
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
	wc_w_add_to_log($uid, $onhold, 0, $order_id);
}

add_action('wp_head', 'wc_m_after_calculate_totals');
add_action( 'rf_get_the_cart', 'wc_m_after_calculate_totals' );

function wc_m_after_calculate_totals(){
	if ( WC()->cart->get_cart_contents_count() == 0 ) {
		set_credit_in_cart( 0 );
	}
}

add_action('wp_head', 'wc_w_on_update');
function wc_w_on_update(){
	if ( is_checkout() || is_cart() || defined('WOOCOMMERCE_CHECKOUT') || defined('WOOCOMMERCE_CART') ) {
		if( is_user_logged_in() ){
			$amount = get_user_meta( get_current_user_id(), 'wc_wallet', true );
			if(isset($_POST['wc_w_field']) && $_POST['wc_w_field'] !== null && $_POST['wc_w_field'] != ""){
				$credit 	= $_POST['wc_w_field'];
				$on_hold = get_user_meta( get_current_user_id(), 'onhold_credits',true ) != 0 ? get_user_meta( get_current_user_id(), 'onhold_credits',true ) : 0;
				$cart_total = bindec($_POST['Wc_total']);
				$in_wallet	= $amount;
			
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
		}
	}
}

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
	$e	 = new wc_w();
	global $wpdb;
	$wpdb->insert( 
		$e->db_name, 
		array( 
				'wcw_type' => $method, 
				'uid' => $uid, 
				'date'	=> date('d M Y'), 
				'oid' => $order_id,
				'amount' => $amount
		), 
		array( 
				'%d',
				'%d',
				'%s',
				'%d',
				'%d'
		) 
	);
}


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
 * @return array
 */
function wc_w_get_log(){
	$e	 = new wc_w();
	$items = array();
	$con = mysqli_connect( DB_HOST, DB_USER, DB_PASSWORD, DB_NAME );
	$sql = "select * from ".$e->db_name;
	$result	=	mysqli_query( $con, $sql );
	$i = 0;
	while($row = mysqli_fetch_assoc($result)){
		$items[$i]['date']	=	$row['date'];
		$items[$i]['oid']	=	$row['oid'];
		$items[$i]['uid']	=	$row['uid'];
		$items[$i]['amount']=	$row['amount'];
		$items[$i]['ID']	=	$row['ID'];
		$items[$i]['wcw_type']	=	$row['wcw_type'];
		$i++;
	}
	return $items;
}
?>