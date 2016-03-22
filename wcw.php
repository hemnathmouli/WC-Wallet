<?php
/**
 * Plugin Name: WC Wallet
 * Plugin URI: http://hemnathmouli.github.io/WC-Wallet
 * Author: Hemnath Mouli
 * Author URI: http://hemzware.com
 * Description: Activate this plugin to make the wallet system with woocommerce.!
 * Version: 1.0
 */

if ( ! defined( 'WPINC' ) ) { die; }
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
	public $db_name		=	"wp_wc_wallet";
	
	/**
	 *
	 * @var Table name for the wallet in db
	 */
	public $db_cancel	=	"wp_wc_wallet_cancel_order";
	
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
		register_activation_hook(__FILE__, array( $this, 'add_options') );
		add_action( 'init', array( $this, 'wc_w_add_post_type' ), 0 );
		add_action( 'woocommerce_order_status_cancelled', array($this, 'wc_m_move_order_money_to_user') );
	}
	
	/**
	 * 
	 * @todo All the setting options
	 */
	function add_options(){
		add_option('wcw_payment_method');
		add_option('wcw_apply_tax', 1);
		add_option('wcw_restrict_max','');
		add_option('wcw_notify_admin',  1);
		add_option('wcw_remining_credits', 1);
		add_option('wcw_cancel_req', 1);
		add_option('wcw_automatic_cancel_req');
		add_option('wcw_notify_on_cancel_req', 1);
	}
	
	/**
	 * 
	 * @todo Create menu
	 */
	function wc_w_add_menus(){
		$e = new wc_w();
		add_menu_page('Wallet', 'WC Wallet', 'administrator', 'wallet', array( wc_w, 'wc_w_menu_content' ), 'dashicons-nametag', '56' );
		add_submenu_page( 'wallet', 'Credits logs','Credits logs', 'administrator', 'wallet', array( wc_w, 'wc_w_menu_content' ) );
		add_submenu_page( 'wallet', 'Cancel Requests', 'Cancel Requests '.$e->request_count( 10 ), 'administrator', 'wc-wallet-cancel-requests', array( wc_w, 'wc_w_menu_cancel_request' ) );
		add_submenu_page( 'wallet', 'Settings', 'Settings', 'administrator', 'wc-wallet-settings', array( wc_w, 'wc_w_menu_settings' ) );
	}

	function wc_w_add_post_type(){
		
			$labels = array(
					'name'                => _x( 'Cancel Order Request', 'Cancel Order', 'wcw_cancel_order_request' ),
					'singular_name'       => _x( 'Cancel Order Request', 'Cancel Order', 'wcw_cancel_order_request' ),
					'menu_name'           => __( 'Cancel Order', 'wcw_cancel_order_request' ),
					'parent_item_colon'   => __( 'Parent Cancel Order', 'wcw_cancel_order_request' ),
					'all_items'           => __( 'All Cancel Order', 'wcw_cancel_order_request' ),
					'view_item'           => __( 'View Cancel Order', 'wcw_cancel_order_request' ),
					'add_new_item'        => __( 'Add New Cancel Order', 'wcw_cancel_order_request' ),
					'add_new'             => __( 'Add New', 'wcw_cancel_order_request' ),
					'edit_item'           => __( 'Edit Cancel Order', 'wcw_cancel_order_request' ),
					'update_item'         => __( 'Update Cancel Order', 'wcw_cancel_order_request' ),
					'search_items'        => __( 'Search Cancel Order', 'wcw_cancel_order_request' ),
					'not_found'           => __( 'Not Found', 'wcw_cancel_order_request' ),
					'not_found_in_trash'  => __( 'Not found in Trash', 'wcw_cancel_order_request' ),
			);
		
			// Set other options for Custom Post Type
		
			$args = array(
					'label'               => __( 'Cancel Order Request', 'wcw_cancel_order_request' ),
					'description'         => __( 'Cancel Order Request', 'wcw_cancel_order_request' ),
					'labels'              => $labels,
					// Features this CPT supports in Post Editor
					'supports'            => array( 'title', 'editor' ),
					'taxonomies'          => array( 'genres' ),
					'hierarchical'        => false,
					'public'              => true,
					'show_ui'             => true,
					'show_in_menu'        => false,
					'show_in_nav_menus'   => true,
					'show_in_admin_bar'   => true,
					'can_export'          => true,
					'has_archive'         => true,
					'exclude_from_search' => false,
					'publicly_queryable'  => true,
					'capability_type'     => 'page',
			);
			register_post_type( 'wcw_corequest', $args );
		
		
	}
	
	function wc_w_menu_content(){
		$e = new wc_w();
		include_once $e->log.'backend.php';
	}
	
	function wc_w_menu_settings(){
		$e = new wc_w();
		include_once $e->includes.'settings.php';
	}
	
	function wc_w_menu_cancel_request(){
		$e = new wc_w();
		include_once $e->includes.'cancel_requests.php';
	}
	
	function require_wc_w_files(){
		include_once $this->includes.'functions.php';
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
		if( $order->get_status() == "processing" || $order->get_status() == "completed" ){
			foreach( $ttyl as $key => $yl ){
				if( $yl['name'] == "Credits" ){
					$e = $key;
					break;
				}
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
				update_user_meta( $order_autho, 'wc_wallet', $author_wallet + $order_total );
				wc_w_add_to_log( $order_autho, $order_total, 1, $order_id );
			}
		}
	}
	
	/**
	 * 
	 * @param int $count
	 * @return string
	 * @todo Adds counts to the menu
	 */
	function request_count( $count ){
		if( $count != 0 ){
			return '<span class="update-plugins count-'.$count.'"><span class="plugin-count">'.$count.'</span></span>';
		}
	}
	
	
}

if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	new wc_w();
} else {
	add_action( 'admin_notices', 'wc_wallet_notice' );
}

function wc_wallet_notice() {
	echo '<div class="error"><p><strong> <i> WC Wallet </i> </strong> Requires <a href="'.admin_url( 'plugin-install.php?tab=plugin-information&plugin=woocommerce').'"> <strong> <u>Woocommerce</u></strong>  </a> To Be Installed And Activated </p></div>';
}

?>