<?php
/**
 * International response handle
 * 
 * @author Viet Artisans
 * @since  1.0.2
 */
namespace WooOnePay\Gateways;

use WooOnePay\Responses\WooOnepayResponse;
use WooOnePay\Gateways\WooOnePayGateway;

class WooOnePayInternationalResponse extends WooOnepayResponse
{
	public function __construct()
	{
		$this->hashCode = '6D0870CDE5F24F34F3915FB0045120DB';
		parent::__construct();
	}

	public function getResponseDescription($responseCode)
	{
	    switch ($responseCode) {
	        case "0" :
	            $result = "Transaction Successful";
	            break;
	        case "?" :
	            $result = "Transaction status is unknown";
	            break;
	        case "1" :
	            $result = "Bank system reject";
	            break;
	        case "2" :
	            $result = "Bank Declined Transaction";
	            break;
	        case "3" :
	            $result = "No Reply from Bank";
	            break;
	        case "4" :
	            $result = "Expired Card";
	            break;
	        case "5" :
	            $result = "Insufficient funds";
	            break;
	        case "6" :
	            $result = "Error Communicating with Bank";
	            break;
	        case "7" :
	            $result = "Payment Server System Error";
	            break;
	        case "8" :
	            $result = "Transaction Type Not Supported";
	            break;
	        case "9" :
	            $result = "Bank declined transaction (Do not contact Bank)";
	            break;
	        case "A" :
	            $result = "Transaction Aborted";
	            break;
	        case "C" :
	            $result = "Transaction Cancelled";
	            break;
	        case "D" :
	            $result = "Deferred transaction has been received and is awaiting processing";
	            break;
	        case "F" :
	            $result = "3D Secure Authentication failed";
	            break;
	        case "I" :
	            $result = "Card Security Code verification failed";
	            break;
	        case "L" :
	            $result = "Shopping Transaction Locked (Please try the transaction again later)";
	            break;
	        case "N" :
	            $result = "Cardholder is not enrolled in Authentication scheme";
	            break;
	        case "P" :
	            $result = "Transaction has been received by the Payment Adaptor and is being processed";
	            break;
	        case "R" :
	            $result = "Transaction was not processed - Reached limit of retry attempts allowed";
	            break;
	        case "S" :
	            $result = "Duplicate SessionID (OrderInfo)";
	            break;
	        case "T" :
	            $result = "Address Verification Failed";
	            break;
	        case "U" :
	            $result = "Card Security Code Failed";
	            break;
	        case "V" :
	            $result = "Address Verification and Card Security Code Failed";
	            break;
			case "99" :
	            $result = "User Cancel";
	            break;
	        default  :
	            $result = "Unable to be determined";
	    }
	    return $result;
	}

	/**
	 * get return page of domestic
	 * 
	 * @return int
	 */
	public function thankyou()
	{
		$gateway = new WooOnePayGateway;
		return $gateway->get_option('receipt_return_url'); 
	}

}