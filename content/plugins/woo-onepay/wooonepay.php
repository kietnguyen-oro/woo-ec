<?php
/**
 * Plugin Name: Woo OnePay
 * Description: Integrate Onepay paygate into Woocommerce
 * Version: 1.0.2
 * Author: Viet Artisans
 * Author URI: https://vietartisans.io
 * License: MIT
 */

use WooOnePay\Gateways\WooOnePayGateway;
use WooOnePay\Gateways\WooOnePayDomestic;
use WooOnePay\Gateways\WooOnePayDomesticResponse;
use WooOnePay\Gateways\WooOnePayInternationalResponse;
use WooOnePay\Traits\Pages;

require 'vendor/autoload.php';
//require 'src/return.php';

/**
 * Launch plugin
 * 
 * @package wooonepay
 * @author  Viet Artisans
 * @since   1.0.0
 */
class WooOnePay
{
	use WooOnePay\Traits\Pages;

	/**
	 * Attach shortcodes into main plugin
	 * 
	 * @var array $shortcodes
	 */
	protected $shortcodes = array();

	/**
	 * @var array
	 */
	protected $responses;

	public function __construct()
	{
		$this->constants();
		add_action('init', array($this, 'renderPages'));
		add_action('plugins_loaded', array($this, 'wooOnepayInit'));
		add_filter('woocommerce_locate_template', array($this, 'onepayWoocommerceTemplates'), 10, 3);
		$this->loadModule();
		$this->responseListener();
	}

	/**
	 * 
	 * 
	 * @return void
	 */
	public function constants()
	{
		$consts = array(
			'URL' => plugins_url('', __FILE__),
			'IMAGE' => plugins_url('/images', __FILE__)
		);

		foreach ($consts as $key => $value) {
			define($key, $value);
		}
	}

	/**
	 * init
	 * 
	 * @return void
	 */
	public function wooOnepayInit()
	{
		add_filter('woocommerce_payment_gateways', array($this, 'addPaymentMethod'));
	}

	/**
	 * Add payment way to woocommerce
	 * 
	 * @param  $methods
	 * @return array $methods
	 */
	public function addPaymentMethod($methods)
	{
		$methods[] = 'WooOnePay\Gateways\WooOnePayGateway';
		$methods[] = 'WooOnePay\Gateways\WooOnePayDomestic';
		return $methods;
	}

	/**
	 * load module
	 * 
	 * @return void
	 */
	public function loadModule()
	{
		//$this->shortcodes[] = new WooOnePay\Shortcodes\Thankyou;
	}

	/**
	 * 
	 * @return void
	 */
	public function responseListener()
	{
		if (isset($_GET['type'])) {
			switch ($_GET['type']) {
				case 'international':
					$this->responses[] = new WooOnePayInternationalResponse;
					break;
				case 'domestic':
					$this->responses[] = new WooOnePayDomesticResponse;
					break;		
			}
		}
	}

	/**
	 * render page
	 * 
	 * @return void
	 */
	public function renderPages()
	{
		$checkRenderPage = (!get_option('wooonepay_settings')) ? false : get_option('wooonepay_settings');
		if ($checkRenderPage != false) return;
		if (!empty($this->pages)) {
			foreach ($this->pages as $slug => $args) {
				$page = new WooOnePay\Page($args);
			}
			update_option('wooonepay_settings', true);
		}
	}

	/**
	 * override WooCommerce templates
	 * 
	 * @param  $template
	 * @param  $template_name
	 * @param  $template_path
	 * @return $template
	 */
	public function onepayWoocommerceTemplates($template, $template_name, $template_path)
	{
		global $woocommerce;

		$_template = $template;

		if (!$template_path) $template_path = $woocommerce->template_url;

		$plugin_path  = __DIR__ . '/woocommerce/';

		// Look within passed path within the theme - this is priority
		$template = locate_template(
			array(
			  $template_path . $template_name,
			  $template_name
			)
		);

		// Modification: Get the template from this plugin, if it exists
		if (!$template && file_exists( $plugin_path . $template_name))

		$template = $plugin_path . $template_name;

		// Use default template
		if (!$template) $template = $_template;

		// Return what we found
		return $template;
	}
}

// kick it off
new WooOnePay;