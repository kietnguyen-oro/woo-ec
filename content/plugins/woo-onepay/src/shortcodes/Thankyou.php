<?php
/**
 * create thank you shortcode
 * 
 * @author Viet Artisans
 * @since  1.0.0
 */

namespace WooOnePay\Shortcodes;

class Thankyou
{
	public function __construct()
	{
		add_shortcode('wooonepay_thankyou', array($this, 'callback'));
	}

	/**
	 * callbackable method
	 * 
	 * @param array $atts
	 * @return mixed
	 */
	public function callback($atts)
	{
		global $woocommerce;
		ob_start();
		$opts = shortcode_atts(
			array(

			),
			$atts
		);
		
		return ob_get_clean();
	}

}