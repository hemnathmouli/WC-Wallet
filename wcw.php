<?php
/**
 * Plugin Name: WC Wallet
 * Plugin URI: http://hemnathmouli.github.io/WC-Wallet
 * Author: Hemnath Mouli
 * Author URI: http://hemzware.com
 * Description: Activate this plugin to make the wallet system with woocommerce products.!
 * Version: 1.0
 */

class wc_w{
	
	/**
	 * 
	 * @var The current version of the plugin
	 */
	private $version 	= '1.0';
	
	/**
	 * 
	 * @var INCLUDES folder
	 */
	private $includes 	= 'includes/';
	
	/**
	 * 
	 * @var LOG folder
	 */
	private $log 		= 'log/';
	
	/**
	 * 
	 * @var Table name for the wallet in db
	 */
	public $db_name	=	"wp_wc_wallet";
	
	function __CONSTRUCT(){
		$this->require_wc_w_files();
		$this->hooks();
	}
	
	/**
	 * 
	 * @todo Add all the actions, filter or hook
	 */
	function hooks(){
		add_action( 'admin_menu', array(__CLASS__, 'wc_w_add_menus'), 5 );
		register_activation_hook(__FILE__, array( $this, 'wc_w_db_init') );
		add_action( 'woocommerce_order_status_cancelled', array($this, 'wc_m_move_order_money_to_user') );
	}
	
	/**
	 * 
	 * @todo Create menu
	 */
	function wc_w_add_menus(){
		add_menu_page('Wallet', 'WC Wallet', 'administrator', 'wallet', array( wc_w, 'wc_w_menu_content' ), 'dashicons-nametag', '56' );
		add_submenu_page('wallet', 'Wallet / Credits logs', 'Wallet / Credits logs', 'administrator', 'wallet', array( wc_w, 'wc_w_menu_content' ));
	}
	
	function wc_w_menu_content(){
		$e = new wc_w();
		include_once $e->log.'backend.php';
	}
	
	function require_wc_w_files(){
		include_once $this->includes.'functions.php';
	}
	
	function wc_w_db_init(){
		global $wpdb;
		$table_name = $this->db_name;
		$sql = "CREATE TABLE $table_name (
		`ID` int NOT NULL AUTO_INCREMENT,
		`wcw_type` varchar(100) NOT NULL,
		`uid` int(25) NOT NULL,
		`date` varchar(100) NULL,
		`oid` int(25) NULL,
		`amount` varchar(100) NULL,
		PRIMARY KEY(ID)
		);";
		require_once (ABSPATH . 'wp-admin/includes/upgrade.php');
		dbDelta($sql);
	}
	
	/**
	 * 
	 * @param int $order_id
	 * @todo This function is triggered, when a order is cancelled in woocommerce.
	 */
	function wc_m_move_order_money_to_user( $order_id ){
		$order_total = get_post_meta($order_id, '_order_total', true);
		$order = wc_get_order( $order_id );
		$ttyl = $order->get_fees();
		foreach( $ttyl as $key => $yl ){
			$e = $key;
			break;
		}
		$c = (string)$e;
		
		if( -1*$ttyl[$c]['line_total'] ){
			$order_autho = get_post_meta($order_id, '_customer_user', true);
			$author_wallet = get_user_meta( $order_autho, 'wc_wallet', true );
			update_user_meta( $order_autho, 'wc_wallet', $author_wallet+$order_total+(-$ttyl[$c]['line_total']) );
			wc_w_add_to_log($order_autho, $order_total+(-$ttyl[$c]['line_total']), 1, $order_id);
		}else{
			$order_autho = get_post_meta($order_id, '_customer_user', true);
			$author_wallet = get_user_meta( $order_autho, 'wc_wallet', true );
			update_user_meta( $order_autho, 'wc_wallet', $author_wallet+$order_total );
			wc_w_add_to_log($order_autho, $order_total, 1, $order_id);
		}
	}
	
	
}

new wc_w();
?>