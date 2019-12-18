=== WC Wallet ===
Contributors: hemnathmouli
Donate link: https://www.paypal.me/hemmyy/
Tags: wc wallet, wc credits, woocommerce wallet, cancelled order to wallet, woocommerce credits 
Requires at least: 4.0
Tested up to: 5.2.1
Stable tag: 2.1.0
Requires PHP: 5.2.4
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html

It allows you to use credit instead of paying money. The credits are added to the user's wallet from the previous canceled orders.

== Description ==

If you ever don't want to refund your money instead of use as credits for future purchases, here is the plugin. The credits will be added at a discount. This plugin is flexible with cart taxes and coupons.

= How it Works =
* A order is made with a payment
* When the user insists to cancel the order and the order is cancelled
* Now the order total has not been refunded instead added to the user's wallet for future purchase
* The credits will be subtracted from the wallet for every purchase, as per credits used.

= Features =
* User Interfaced to add credits on cart page.
* No need for refunds in the gateways.
* Automatic update total when credit is larger the cart total or when product is deleted.
* Send Cancel Order Request and let the admin refund the amount as Credits.
* Credits restrictions for users.
* To show users their balance, use `[wc_wallet_show_balance]` shortcode.
* Offer credits for new users.
* Check the history of transfers and also balance in My Account
* Dashboard Widget.

== Installation ==
1. Ensure you have the latest version of WooCommerce plugin installed (2.2 or above)
2. Unzip and upload contents of the plugin to your /wp-content/plugins/ directory
3. Activate the plugin through the 'Plugins' menu in WordPress

== Screenshots ==
1. WC Wallet Cancel Request Page
2. WC Wallet Settings Page
3. WC Wallet Cart view
4. WC Wallet My order with "Send Cancel Request"

== Changelog ==

= 1.0.0 =
* First Public Release.

= 1.0.1 =
* Filter updater and Bugfix

= 1.0.2 =
* New Features ( Cancel by order status )
* Bug Fix

= 1.0.3 =
* Bug Fix

= 1.0.4 =
* Float value updates

= 1.0.5 =
* Bug fix
* Offer credits for new users
* Added .pot for translation

= 1.0.6 =
* Bug Fix
* Spelling Correction of General to General

= 1.0.7 =
* Bug Fix
* Removed period before exclamation sign
* Dashboard widget
* My Account Menu

= 1.0.8 =
* Big Boss Support
* Cart and Checkout bug fix

= 2.0.0 =
* Able to delete credit logs
* Show logs in user profile if changed by admin
* Bug fixed my account wallet history
* Show credits in Checkout
* Able to clean all logs
* Cancel request bug fix
* Hide wallet form in Cart/Checkout option
* Shortcode [wc_wallet_show_balance] bug fixed

= 2.1.0 =
* Lastest WordPress and Woocommerce compatibility
* Fixed Bugs

== Upgrade Notice ==

= 2.1.0 =
Major upgrade to 2.1.0 is available bug fixes and compatibility with latest Woocommerce version.

= 2.2.0-beta =
* Added filter to change "Wallet" name in my-account tab
* Added option enable/disable to delete Cancel Request when the order is deleted