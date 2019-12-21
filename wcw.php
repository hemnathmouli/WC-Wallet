<?php
/**
 * Plugin Name: WC Wallet
 * Plugin URI: http://hemnathmouli.github.io/WC-Wallet
 * Author: Hemnath Mouli
 * Author URI: http://hemzware.com
 * Description: Activate this plugin to make the wallet system with WooCommerce.!
 * Version: 2.2.0
 * Text Domain: wc-wallet
 */

if ( ! defined( 'WPINC' ) ) { die; }

define( "WC_WALLET_TEXT", "wc-wallet" );

class wc_w {
	
	/**
	 * 
	 * @var The current version of the plugin
	 */
	private $version 	= '2.2.0';
	
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
	

	
	function __CONSTRUCT(){
		$this->require_wc_w_files();
		//$this->hooks();
		add_action( 'init', array( $this, 'wc_w_add_post_type' ), 0 );
	}
	
	/**
	 * 
	 * @todo All the setting options
	 */
	function add_options(){
		add_option('wcw_payment_method');
		add_option('wcw_transfer_only');
		add_option('wcw_apply_tax', 1);
		add_option('wcw_restrict_max','');
		add_option('wcw_notify_admin',  1);
		add_option('wcw_remining_credits', 1);
		add_option('wcw_cancel_req', 1);
		add_option('wcw_automatic_cancel_req');
		add_option('wcw_is_float_value', 0);
		add_option('wcw_show_in_myaccount', 1);
		add_option('wcw_show_in_cart', 0);
		add_option('wcw_show_in_checkout', 1);
		add_option('wcw_remove_cancel_logs', 1);
	}
	
	/**
	 * 
	 * @todo Create menu
	 */
	function wc_w_add_menus(){
		$e = new wc_w();
		add_menu_page('Wallet', 'WC Wallet', 'administrator', 'wallet', array( $this, 'wc_w_menu_content' ), 'dashicons-nametag', '56' );
		add_submenu_page( 'wallet', 'Credits logs','Credits logs', 'administrator', 'wallet', array( $this, 'wc_w_menu_content' ) );
		add_submenu_page( 'wallet', 'Cancel Requests', 'Cancel Requests '.$e->request_count(), 'administrator', 'wc-wallet-cancel-requests', array( $this, 'wc_w_menu_cancel_request' ) );
		add_submenu_page( 'wallet', __('Settings', WC_WALLET_TEXT), __('Settings', WC_WALLET_TEXT), 'administrator', 'wc-wallet-settings', array( $this, 'wc_w_menu_settings' ) );
	}
	
	/**
	 * 
	 * @todo Create the post types
	 */
	function wc_w_add_post_type() {
		
			$labels = array(
					'name'                => _x( 'Cancel Order Request', 'Cancel Order', WC_WALLET_TEXT ),
					'singular_name'       => _x( 'Cancel Order Request', 'Cancel Order', WC_WALLET_TEXT ),
					'menu_name'           => __( 'Cancel Order', WC_WALLET_TEXT ),
					'parent_item_colon'   => __( 'Parent Cancel Order', WC_WALLET_TEXT ),
					'all_items'           => __( 'All Cancel Order', WC_WALLET_TEXT ),
					'view_item'           => __( 'View Cancel Order', WC_WALLET_TEXT ),
					'add_new_item'        => __( 'Add New Cancel Order', WC_WALLET_TEXT ),
					'add_new'             => __( 'Add New', WC_WALLET_TEXT ),
					'edit_item'           => __( 'Edit Cancel Order', WC_WALLET_TEXT ),
					'update_item'         => __( 'Update Cancel Order', WC_WALLET_TEXT ),
					'search_items'        => __( 'Search Cancel Order', WC_WALLET_TEXT ),
					'not_found'           => __( 'Not Found', WC_WALLET_TEXT ),
					'not_found_in_trash'  => __( 'Not found in Trash', WC_WALLET_TEXT ),
			);
		
			// Set other options for Custom Post Type
		
			$args = array(
					'label'               => __( 'Cancel Order Request', WC_WALLET_TEXT ),
					'description'         => __( 'Cancel Order Request', WC_WALLET_TEXT ),
					'labels'              => $labels,
					// Features this CPT supports in Post Editor
					'supports'            => array( 'title', 'editor' ),
					'taxonomies'          => array( 'genres' ),
					'hierarchical'        => false,
					'public'              => true,
					'show_ui'             => true,
					'show_in_menu'        => false,
					'show_in_nav_menus'   => true,
					'show_in_admin_bar'   => false,
					'can_export'          => true,
					'has_archive'         => true,
					'exclude_from_search' => false,
					'publicly_queryable'  => true,
					'capability_type'     => 'page',
			);
			register_post_type( 'wcw_corequest', $args );
			
			$labels = array(
					'name'                => _x( 'WC log', 'WC log', WC_WALLET_TEXT ),
					'singular_name'       => _x( 'WC log', 'WC log', WC_WALLET_TEXT ),
					'menu_name'           => __( 'WC log', WC_WALLET_TEXT ),
					'parent_item_colon'   => __( 'Parent WC log', WC_WALLET_TEXT ),
					'all_items'           => __( 'All WC log', WC_WALLET_TEXT ),
					'view_item'           => __( 'View WC log', WC_WALLET_TEXT ),
					'add_new_item'        => __( 'Add New WC log', WC_WALLET_TEXT ),
					'add_new'             => __( 'Add New WC log', WC_WALLET_TEXT ),
					'edit_item'           => __( 'Edit WC log', WC_WALLET_TEXT ),
					'update_item'         => __( 'Update WC log', WC_WALLET_TEXT ),
					'search_items'        => __( 'Search WC log', WC_WALLET_TEXT ),
					'not_found'           => __( 'Not Found', WC_WALLET_TEXT ),
					'not_found_in_trash'  => __( 'Not found in Trash', WC_WALLET_TEXT ),
			);
			
			// Set other options for Custom Post Type
			
			$args = array(
					'label'               => __( 'WC log', WC_WALLET_TEXT ),
					'description'         => __( 'WC log', WC_WALLET_TEXT ),
					'labels'              => $labels,
					// Features this CPT supports in Post Editor
					'supports'            => array( 'title', 'editor' ),
					'taxonomies'          => array( 'genres' ),
					'hierarchical'        => false,
					'public'              => true,
					'show_ui'             => true,
					'show_in_menu'        => false,
					'show_in_nav_menus'   => true,
					'show_in_admin_bar'   => false,
					'can_export'          => true,
					'has_archive'         => true,
					'exclude_from_search' => false,
					'publicly_queryable'  => true,
					'capability_type'     => 'page',
			);
			register_post_type( WC_WALLET_TEXT, $args );
		
		
			add_action( 'admin_menu', array($this, 'wc_w_add_menus'), 5 );
			register_activation_hook(__FILE__, array( $this, 'add_options') );
			add_action( 'init', array( $this, 'wc_wallet_setup' ) );
			//add_action( 'woocommerce_order_status_cancelled', array($this, 'wc_m_move_order_money_to_user') );
			add_action( 'woocommerce_order_status_changed', array( $this, 'wc_m_move_order_money_to_user'), 99, 3 );
			add_filter( 'plugin_row_meta', array( $this, 'wcw_plugin_row_meta' ), 10, 2 );
			add_action( 'trashed_post', array($this, 'wcw_remove_cancel_request'), 10, 1);
	}
	
	/**
	 * 
	 * @todo Get logs page
	 */
	function wc_w_menu_content(){
		$e = new wc_w();
		include_once $e->log.'backend.php';
	}
	
	/**
	 * 
	 * @todo Get settings page
	 */
	function wc_w_menu_settings(){
		$e = new wc_w();
		include_once $e->includes.'settings.php';
	}
	
	/**
	 * 
	 * @todo Cancel Request Page
	 */
	function wc_w_menu_cancel_request(){
		$e = new wc_w();
		include_once $e->includes.'cancel_requests.php';
	}
	
	/**
	 * 
	 * @todo Import the functions
	 */
	function require_wc_w_files(){
		include_once $this->includes.'functions.php';
	}
	
	/**
	 * 
	 * @param int $order_id
	 * @todo This function is triggered, when a order is cancelled in woocommerce.
	 */
	function wc_m_move_order_money_to_user( $order_id, $old_status, $new_status ){
		if( $new_status != "cancelled" ) {
			return;
		}
		if( array_search( $order_id, get_the_order_in_log() ) === false ) {
			$opm	=	(array_search( get_post_meta( $order_id, '_payment_method', true ), get_wcw_only_methods() ) !== false || get_post_meta( $order_id, '_payment_method', true ) == "" ) ? true : false; 
			if( count(get_wcw_only_methods()) != 0 && $opm && wcw_check_the_order_status( $order_id, $old_status ) ){
				$order_total = get_post_meta($order_id, '_order_total', true);
				$order = wc_get_order( $order_id );
				$ttyl = $order->get_fees();
				
				foreach( $ttyl as $key => $yl ){
					if( $yl['name'] == "Credits" ){
						$e = $key;
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
	}

	/**
	 * Removes cancel request when related order is deleted (only if the option is enabled)
	 */
	function wcw_remove_cancel_request( $post_id = '' ) {
		global $post_type;

		if ( $post_type == 'shop_order' ) {
			if ( $post_id != '' && is_wcw_remove_cancel_logs() ) {
				$args = array(
					'posts_per_page'	=>	-1,
					'post_type'        	=> 	'wcw_corequest',
					'meta_query' => array(
						array(
							'key' => 'oid',
							'value' => $post_id,
							'compare' => '=',
						)
					)
				);
				$posts = get_posts($args);

				if ( count( $posts ) ) {
					foreach ( $posts as $order_request ) {
						wp_delete_post( $order_request->ID );
					}
				}
			}
		}
	}
	
	/**
	 * 
	 * @param int $count
	 * @return string
	 * @todo Adds counts to the menu
	 */
	function request_count(){
		$count = get_count_cancel_request();
		if( $count != 0 ){
			return '<span class="update-plugins count-'.$count.'"><span class="plugin-count">'.$count.'</span></span>';
		}
	}
	
	function wc_wallet_setup () {
		/*
		 * Make plugin available for translation.
		 * Translations can be filed in the /languages/ directory.
		 */
		load_plugin_textdomain( 'wc-wallet', false, dirname( plugin_basename( __FILE__ ) ) . '/languages' );
	}
		
	function wcw_plugin_row_meta( $links, $file ) {
		
		if ( strpos( $file, 'wcw.php' ) !== false ) {
			$new_links = array(
					'donate' 	=> '<b><a href="https://www.paypal.me/hemmyy/" target="_blank">Donate</a></b>',
					'support'	=> '<a href="https://wordpress.org/support/plugin/wc-wallet" target="_blank">Support</a>',
					'hire_me' 	=> '<a href="http://hemzware.com/hire-me" target="_blank">Hire me</a>'
			);
			
			$links = array_merge( $links, $new_links );
		}
		
		return $links;
	}
	
	
}

if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	new wc_w();
} else {
	add_action( 'admin_notices', 'wc_wallet_notice' );
}

/**
 * 
 * @todo Notice admin to activate WooCommerce Plugin
 */
function wc_wallet_notice() {
	echo '<div class="error"><p><strong> <i> WC Wallet </i> </strong> '.__('Requires', WC_WALLET_TEXT).' <a href="'.admin_url( 'plugin-install.php?tab=plugin-information&plugin=woocommerce').'"> <strong> <u>Woocommerce</u></strong>  </a> '.__('To Be Installed And Activated', WC_WALLET_TEXT).' </p></div>';
}

?>