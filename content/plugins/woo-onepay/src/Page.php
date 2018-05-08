<?php
/**
 * create neccessary pages for payment
 * 
 * @author Viet Artisans
 * @since  1.0.1
 */

namespace WooOnePay;

class Page
{
	/**
	 * @var array $args
	 */
	protected $args;

	public function __construct($args)
	{
		// this is secret
		$this->args = $args;
		$this->createPage();
	}

	public function createPage()
	{
		return wp_insert_post($this->args);
	}
}