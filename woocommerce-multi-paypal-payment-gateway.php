<?php
/* @wordpress-plugin
 * Plugin Name:       WooCommerce Multi Paypal Payment Gateway
 * Description:       The plugin allows the use of multiple Paypal accounts in the same shop.
 * Version:           1.0.0
 * WC requires at least: 3.0
 * WC tested up to: 5.2
 * Author:            TungPG
 * Text Domain:       infipay-multi-paypal-checkout-payment-gateway
 * Domain Path: /languages
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

$active_plugins = apply_filters('active_plugins', get_option('active_plugins'));
if(wpruby_custom_payment_is_woocommerce_active()){
	add_filter('woocommerce_payment_gateways', 'add_multi_paypal_checkout_payment_gateway');
	function add_multi_paypal_checkout_payment_gateway( $gateways ){
		$gateways[] = 'WC_Multi_Paypal_Checkout_Payment_Gateway';
		return $gateways; 
	}

	add_action('plugins_loaded', 'init_multi_paypal_checkout_payment_gateway');
	function init_multi_paypal_checkout_payment_gateway(){
		require 'class-infipay-multi-paypal-checkout-payment-gateway.php';
	}

	add_action( 'plugins_loaded', 'multi_paypal_checkout_payment_load_plugin_textdomain' );
	function multi_paypal_checkout_payment_load_plugin_textdomain() {
	  load_plugin_textdomain( 'infipay-multi-paypal-checkout-payment-gateway', FALSE, basename( dirname( __FILE__ ) ) . '/languages/' );
	}



}


/**
 * @return bool
 */
function wpruby_custom_payment_is_woocommerce_active()
{
	$active_plugins = (array) get_option('active_plugins', array());

	if (is_multisite()) {
		$active_plugins = array_merge($active_plugins, get_site_option('active_sitewide_plugins', array()));
	}

	return in_array('woocommerce/woocommerce.php', $active_plugins) || array_key_exists('woocommerce/woocommerce.php', $active_plugins);
}
