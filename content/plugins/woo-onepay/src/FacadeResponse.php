<?php
/**
 * 
 * @author Viet Artisans
 * @since  1.0.2
 */
namespace WooOnePay\Facades;

interface FacadeResponse
{
	/**
	 * 
	 * @param  int|string 
	 * @return string
	 */
	public function getResponseDescription($responseCode);

	/**
	 * 
	 * @param 
	 * @return
	 */
	public function checkResponse($txnResponseCode);

	/**
	 * Check hash
	 * 
	 * @param
	 * @return
	 */
	public function validateHash();

	/**
	 * Get order
	 * 
	 * @param
	 * @return
	 */
	public function getOrder($orderId);
}