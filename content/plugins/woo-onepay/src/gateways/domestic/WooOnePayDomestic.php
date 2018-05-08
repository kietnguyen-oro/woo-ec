<?php
/**
 * Create a domestic payment gateway
 * 
 * @author Viet Artisans
 * @since  1.0.0
 */
namespace WooOnePay\Gateways;

class WooOnePayDomestic extends \WC_Payment_Gateway
{
	/**
	 * A string of hash sha256
	 * 
	 * @var string $hashCode
	 */
	private $hashCode = 'A3EFDFABA8653DF2342E8DAC29B51AF0';

	/**
	 * Initialize
	 * 
	 * @return void
	 */
	public function __construct()
	{
		$this->id				= 'onepay_domestic';
		$this->icon 			= $this->get_option('logo');
		$this->has_fields 		= false;
		$this->method_title     = __('Onepay Domestic', 'woocommerce');
		
		$this->supports         = array(
			'products',
			'refunds'
		);
		
		// Load the settings.
		$this->init_form_fields();
		$this->init_settings();
		
		// Define user set variables
		$this->title = $this->get_option('title');
		$this->description = $this->get_option('description');
		$this->vpc_version = $this->get_option('vpc_version');
		$this->merchant_id = $this->get_option('merchant_id');
		$this->merchant_access_code = $this->get_option('merchant_access_code');
		$this->receipt_return_url = $this->get_option('receipt_return_url');
		$this->currency = $this->get_option('currency');
		$this->locale = $this->get_option('locale');

		if (!$this->isValidCurrency()) {
			$this->enabled = 'no';
		}

		add_action('woocommerce_update_options_payment_gateways_' . $this->id, array(&$this, 'process_admin_options'));
	}

	/**
	 * get pages list
	 * 
	 * @return array $pagesList
	 */
	public function getPagesList()
	{
		$pagesList = array();
		$pages = get_pages();
		if (!empty($pages)) {
			foreach ($pages as $page) {
				$pagesList[$page->ID] = $page->post_title;
			}
		}
		return $pagesList;
	}

	/**
	 * setup the fields for new payment way
	 * 
	 * @return void
	 */
	public function init_form_fields()
	{
		$this->form_fields = array(
			'enabled' => array(
				'title' => __( 'Enable/Disable', 'woocommerce' ),
				'type' => 'checkbox',
				'label' => __( 'Enable Onepay Domestic Paygate', 'woocommerce' ),
				'default' => 'no'
			),
			'title' => array(
				'title' => __( 'Title', 'woocommerce' ),
				'type' => 'text',
				'description' => '',
				'default' => 'Onepay Domestic'
			),
			'logo' => array(
				'title' => __( 'Logo', 'woocommerce' ),
				'type' => 'text',
				'description' => '',
				'default' => IMAGE . '/logo.png',
			),
			'description' => array(
				'title' => __( 'Customer Description', 'woocommerce' ),
				'type' => 'textarea',
				'description' => __( 'Give the customer instructions for paying via Onepay', 'woocommerce' ),
				'default' => __('Pay via Onepay is easy', 'woocommerce')
			),
			'merchant_id' => array(
				'title' => __( 'Merchant ID', 'woocommerce' ),
				'type' => 'text',
				'description' => __('Provided by OnePAY', 'woocommerce'),
				'default' => 'ONEPAY'
			),
			'merchant_access_code' => array(
				'title' => __('Merchant Access Code', 'woocommerce'),
				'type' => 'text',
				'description' => __('Provided by OnePAY', 'woocommerce'),
				'default' => 'D67342C2'
			),
			'currency' => array(
				'title' => __('Currency', ''),
				'type' => 'select',
				'class'       => 'wc-enhanced-select',
				'description' => __('Currency (VND', 'woocommerce'),
				'desc_tip'    => true,
				'default' => 'VND',
				'options' => array(
					'VND' => 'VND',
				)
			),
			'receipt_return_url' => array(
				'title' => __('Success Page', 'woocommerce'),
				'type' => 'select',
				'class'       => 'wc-enhanced-select',
				'description' => __('Choose success page', 'woocommerce'),
				'desc_tip'    => true,
				'default' => '',
				'options' => $this->getPagesList()
			),
			'locale' => array(
				'title' => __('Locale', 'woocommerce'),
				'type' => 'select',
				'class'       => 'wc-enhanced-select',
				'description' => __('Language use on gateway (vn/en)', 'woocommerce'),
				'desc_tip'    => true,
				'default' => 'vn',
				'options' => array(
					'vn' => 'vn',
					'en' => 'en'
				)
			),
		);

	}

	/**
	 * Process the payment and return the result
	 * 
	 * @param int $order_id
	 * @return 
	 */
	public function process_payment($order_id)
	{
		$order = new \WC_Order($order_id);
		return array(
			'result' 	=> 'success',
			'redirect'	=> $this->redirect($order_id)
		);
	}

	/**
	 * Get transaction url
	 * 
	 * @param  int    $order_id
	 * @return string $url
	 */
	public function redirect($order_id)
	{
		$order = new \WC_Order($order_id);
		$amount = number_format($order->order_total, 2, '.', '') * 100;
		$vpcMerchTxnRef = date('YmdHis') . rand();
		$args = array(
			// mandatory info
			'Title'=> 'VPC 3-Party',
			'virtualPaymentClientURL' => 'https://mtf.onepay.vn/onecomm-pay/vpc.op',
			'vpc_Merchant' => $this->merchant_id,
			'vpc_AccessCode' => $this->merchant_access_code,
			'vpc_ReturnURL' => admin_url('admin-ajax.php'). '?action=payment_response&type=domestic',
			'vpc_Version' => '2',
			'vpc_Command' => 'pay',
			'vpc_Locale' => $this->locale,
			'vpc_MerchTxnRef' => $vpcMerchTxnRef,
			'vpc_OrderInfo' => 'ORDER' . $order_id,
			'vpc_Amount' => $amount,
			'vpc_TicketNo' => $_SERVER['REMOTE_ADDR'],
			'vpc_Currency' => $this->currency,
			// shipping info
			'vpc_SHIP_Street01' => $_POST['billing_address_1'],
			'vpc_SHIP_Provice' => $_POST['billing_state'],
			'vpc_SHIP_City' => $_POST['billing_city'],
			'vpc_SHIP_Country' => $_POST['billing_country'],
			'vpc_Customer_Phone' => $_POST['billing_phone'],
			'vpc_Customer_Email' => $_POST['billing_email'],
			'vpc_Customer_Id' => '',
			// billing info
		);

		$SECURE_SECRET = $this->hashCode;

		// add the start of the vpcURL querystring parameters
		// *****************************Lấy giá trị url cổng thanh toán*****************************
		$vpcURL = $args["virtualPaymentClientURL"] . "?";

		// Remove the Virtual Payment Client URL from the parameter hash as we 
		// do not want to send these fields to the Virtual Payment Client.
		// bỏ giá trị url và nút submit ra khỏi mảng dữ liệu
		unset($args["virtualPaymentClientURL"]);

		//$stringHashData = $SECURE_SECRET; 
		//*****************************Khởi tạo chuỗi dữ liệu mã hóa trống*****************************
		$stringHashData = "";
		// sắp xếp dữ liệu theo thứ tự a-z trước khi nối lại
		// arrange array data a-z before make a hash
		ksort($args);

		//var_dump($args);exit;

		// set a parameter to show the first pair in the URL
		// đặt tham số đếm = 0
		$appendAmp = 0;

		foreach($args as $key => $value) {

		    // create the md5 input and URL leaving out any fields that have no value
		    // tạo chuỗi đầu dữ liệu những tham số có dữ liệu
		    if (strlen($value) > 0) {
		        // this ensures the first paramter of the URL is preceded by the '?' char
		        if ($appendAmp == 0) {
		            $vpcURL .= urlencode($key) . '=' . urlencode($value);
		            $appendAmp = 1;
		        } else {
		            $vpcURL .= '&' . urlencode($key) . "=" . urlencode($value);
		        }
		        //$stringHashData .= $value; *****************************sử dụng cả tên và giá trị tham số để mã hóa*****************************
		        if ((strlen($value) > 0) && ((substr($key, 0,4)=="vpc_") || (substr($key,0,5) =="user_"))) {
				    $stringHashData .= $key . "=" . $value . "&";
				}
		    }
		}
		//*****************************xóa ký tự & ở thừa ở cuối chuỗi dữ liệu mã hóa*****************************
		$stringHashData = rtrim($stringHashData, "&");
		// Create the secure hash and append it to the Virtual Payment Client Data if
		// the merchant secret has been provided.
		// thêm giá trị chuỗi mã hóa dữ liệu được tạo ra ở trên vào cuối url
		if (strlen($SECURE_SECRET) > 0) {
		    //$vpcURL .= "&vpc_SecureHash=" . strtoupper(md5($stringHashData));
		    // *****************************Thay hàm mã hóa dữ liệu*****************************
		    $vpcURL .= "&vpc_SecureHash=" . strtoupper(hash_hmac('SHA256', $stringHashData, pack('H*',$SECURE_SECRET)));
		}

		//var_dump($vpcURL);exit;
		return $vpcURL;
	}

	/**
	 * Check if this gateway is enabled and available in the user's country.
	 * 
	 * @return bool
	 */
	public function isValidCurrency() {
		return in_array(get_woocommerce_currency(), array('VND'));
	}

	/**
	 * Admin Panel Options.
	 * - Options for bits like 'title' and availability on a country-by-country basis.
	 *
	 * @since 1.0.0
	 */
	public function admin_options()
	{
		if ($this->isValidCurrency()) {
			parent::admin_options();
		} else {
		?>
			<div class="inline error">
				<p>
				<strong>
				<?php _e('Gateway Disabled', 'woocommerce' ); ?>
				</strong> : 
				<?php 
				_e('Onepay does not support your store currency. Currently, Onepay only supports VND currency.', 'woocommerce' );
				?>
				</p>
			</div>
		<?php
		}
	}
}