<?php
/**
 * 
 * 
 * @author Viet Artisans
 * @since  1.0.2
 */
namespace WooOnePay\Responses;

use WooOnePay\Facades\FacadeResponse;

abstract class WooOnepayResponse implements FacadeResponse
{
	/**
	 * @var string $hashCode
	 */
	protected $hashCode;

	/**
	 * Initialize
	 * 
	 * @return void
	 */
	public function __construct()
	{
		$this->action();
	}

	public function action()
	{
		add_action('wp_ajax_payment_response', array($this, 'checkResponse'));
		add_action('wp_ajax_nopriv_payment_response', array($this, 'checkResponse'));
	}

	/**
	 * 
	 */
	public function checkResponse($txnResponseCode)
	{
		global $woocommerce;
		$checkoutUrl = $woocommerce->cart->get_checkout_url();
		$successUrl = get_page_link($this->thankyou());
		$hashValidated = $this->validateHash();
		$txnResponseCode = $_GET["vpc_TxnResponseCode"];
		$order = $this->getOrder($_GET["vpc_OrderInfo"]);
		$transStatus = '';
		if ($hashValidated == "CORRECT" && $txnResponseCode == "0") {
			$transStatus = $this->getResponseDescription($txnResponseCode);
			$order->add_order_note(__($transStatus, 'woocommerce'));
			$order->payment_complete($_GET["vpc_MerchTxnRef"]);
			$order->update_status('completed');
			$woocommerce->cart->empty_cart();
			wp_redirect($successUrl . '?message=' . $transStatus);
		} elseif ($hashValidated == "INVALID HASH" && $txnResponseCode == "0"){
			$transStatus = "Transaction is pendding";
			$order->add_order_note(__($transStatus, 'woocommerce'));
			$order->update_status('pending');
			wp_redirect($successUrl . '?message=' . $transStatus);
		} else {
			$transStatus = $this->getResponseDescription($txnResponseCode);
			$order->add_order_note(__($transStatus, 'woocommerce'));
			$order->update_status('failed');
			$error = new \WP_Error('wooonepay_failed', __($transStatus, 'woocommerce'));
			wc_add_wp_error_notices($error);
			wp_redirect($checkoutUrl);
		}
		exit;
	}

	/**
	 * get succes page url
	 * 
	 */
	abstract public function thankyou();

	/**
	 * 
	 * 
	 * @param  int|string
	 * @return string
	 */
	abstract public function getResponseDescription($responseCode);

	/**
	 * 
	 * @return string
	 */
	public function validateHash()
	{
		if (strlen($this->hashCode) > 0 && $_GET["vpc_TxnResponseCode"] != "7" && $_GET["vpc_TxnResponseCode"] != "No Value Returned") {
		    ksort($_GET);
		    //$md5HashData = $SECURE_SECRET;
		    $md5HashData = "";
		    // sort all the incoming vpc response fields and leave out any with no value
		    foreach ($_GET as $key => $value) {
		        if ($key != "vpc_SecureHash" && (strlen($value) > 0) && ((substr($key, 0,4)=="vpc_") || (substr($key,0,5) =="user_"))) {
				    $md5HashData .= $key . "=" . $value . "&";
				}
		    }
			// remove "&"
		    $md5HashData = rtrim($md5HashData, "&");

			if (strtoupper($_GET["vpc_SecureHash"]) == strtoupper(hash_hmac('SHA256', $md5HashData, pack('H*',$this->hashCode)))) {
		        // Secure Hash validation succeeded, add a data field to be displayed
		        // later.
		        $hashValidated = "CORRECT";
		    } else {
		        // Secure Hash validation failed, add a data field to be displayed
		        // later.
		        $hashValidated = "INVALID HASH";
		    }
		} else {
		    // Secure Hash was not validated, add a data field to be displayed later.
		    $hashValidated = "INVALID HASH";
		}

		return $hashValidated;
	}

	/**
	 * get order
	 * 
	 * @param  string   $orderId
	 * @return WC_Order $order
	 */
	public function getOrder($orderId)
	{
		preg_match_all('!\d+!', $orderId, $matches);
		$order = new \WC_Order($matches[0][0]);
		return $order;
	}

}