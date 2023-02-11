<?php 

class WC_Multi_Paypal_Checkout_Payment_Gateway extends WC_Payment_Gateway{

    private $order_status;

	public function __construct(){
		$this->id = 'multi_paypal_checkout_payment';
		$this->method_title = __('Multi Paypal Payment','infipay-multi-paypal-checkout-payment-gateway');
		//$this->title = __('Multi Paypal Payment','infipay-multi-paypal-checkout-payment-gateway');
		$this->has_fields = true;
		$this->init_form_fields();
		$this->init_settings();
		$this->enabled = $this->get_option('enabled');
		$this->sandbox_enabled = $this->get_option('sandbox_enabled');
		$this->multi_paypal_checkout_payment_server_domain = $this->get_option('multi_paypal_checkout_payment_server_domain');
		$this->title = $this->get_option('title');
		$this->description = $this->get_option('description');
		$this->hide_text_box = $this->get_option('hide_text_box');
		$this->text_box_required = $this->get_option('text_box_required');
		$this->order_status = $this->get_option('order_status');
		$this->order_button_text = $this->get_option('order_button_text');
		$this->invoice_id_prefix = $this->get_option('invoice_id_prefix');
		$this->hide_item_title = $this->get_option('hide_item_title');
		$this->hide_sku = $this->get_option('hide_sku');
		$this->allow_countries = strtoupper($this->get_option('allow_countries'));
		
		// Payment icon show at checkout
		$this->icon = plugin_dir_url( __FILE__ ) . 'images/paypal-cards.png';
		
		// Support Refund
		$this->supports[] ='refunds';
		
		add_action('woocommerce_update_options_payment_gateways_'.$this->id, array($this, 'process_admin_options'));
	}

	public function init_form_fields(){
    	    $invoice_id_prefix = get_bloginfo('name');
    	    $invoice_id_prefix = preg_replace('/\s+/', '', $invoice_id_prefix);
    	    $invoice_id_prefix = strtoupper($invoice_id_prefix);
    	    $invoice_id_prefix = str_replace("STORE", "", $invoice_id_prefix);
    	    $invoice_id_prefix = str_replace("SHOP", "", $invoice_id_prefix);
    	    $invoice_id_prefix = $invoice_id_prefix . "-";
    	    
    	    $default_server_domain = "";
    	    if(defined('MULTI_PAYPAL_PAYMENT_SERVER_DOMAIN')){
    	        $default_server_domain = constant('MULTI_PAYPAL_PAYMENT_SERVER_DOMAIN');
    	    }
    	    
			$this->form_fields = array(
			    
			    
				'enabled' => array(
					'title' 		=> __( 'Enable/Disable', 'infipay-multi-paypal-checkout-payment-gateway' ),
					'type' 			=> 'checkbox',
					'label' 		=> __( 'Enable Multi Paypal Payment', 'infipay-multi-paypal-checkout-payment-gateway' ),
					'default' 		=> 'no'
				),
			    
			    'sandbox_enabled' => array(
			        'title' 		=> __( 'Sandbox Enable/Disable', 'infipay-multi-paypal-checkout-payment-gateway' ),
			        'type' 			=> 'checkbox',
			        'label' 		=> __( 'Enable Sandbox Mode', 'infipay-multi-paypal-checkout-payment-gateway' ),
			        'description' 	=> __( 'If enabled, only sandbox Paypal Accounts will be used.', 'infipay-multi-paypal-checkout-payment-gateway' ),
			        'default' 		=> 'no'
			    ),
			    
			    'multi_paypal_checkout_payment_server_domain' => array(
			        'title' 		=> __( 'Tool Server Domain', 'infipay-multi-paypal-checkout-payment-gateway' ),
			        'type' 			=> 'text',
			        'description' 	=> __( 'The domain address of the tool managing multiple paypal accounts. Example: yourtool.com.', 'infipay-multi-paypal-checkout-payment-gateway' ),
			        'default'		=> __( $default_server_domain, 'infipay-multi-paypal-checkout-payment-gateway' ),
			    ),
			    
	            'title' => array(
					'title' 		=> __( 'Method Title', 'infipay-multi-paypal-checkout-payment-gateway' ),
					'type' 			=> 'text',
					'description' 	=> __( 'This controls the title.', 'infipay-multi-paypal-checkout-payment-gateway' ),
					'default'		=> __( 'Paypal Express', 'infipay-multi-paypal-checkout-payment-gateway' ),
				),
				'description' => array(
					'title' => __( 'Customer Message', 'infipay-multi-paypal-checkout-payment-gateway' ),
					'type' => 'textarea',
					'css' => 'width:500px;',
					'default' => 'Pay via PayPal; you can pay with your credit card if you don’t have a PayPal account.',
					'description' 	=> __( 'The message which you want it to appear to the customer in the checkout page.', 'infipay-multi-paypal-checkout-payment-gateway' ),
				),
				'order_status' => array(
					'title' => __( 'Order Status After The Checkout', 'infipay-multi-paypal-checkout-payment-gateway' ),
					'type' => 'select',
					'options' => wc_get_order_statuses(),
					'default' => 'wc-pending',
					'description' 	=> __( 'The default order status if this gateway used in payment.', 'infipay-multi-paypal-checkout-payment-gateway' ),
				),			    
			    'invoice_id_prefix' => array(
			        'title' => __( 'Invoice ID Prefix', 'infipay-multi-paypal-checkout-payment-gateway' ),
			        'type' => 'text',
			        'default' => $invoice_id_prefix,
			        'description' 	=> __( 'Add a prefix to the invoice ID sent to PayPal. This can resolve duplicate invoice problems when working with multiple websites on the same PayPal account.', 'infipay-multi-paypal-checkout-payment-gateway' ),
			    ),
			    'hide_item_title' => array(
			        'title' 		=> __( 'Hide item title', 'woocommerce-other-payment-gateway' ),
			        'type' 			=> 'checkbox',
			        'default' 		=> 'yes',
			        'description' 	=> __( 'Hide the title of the product when submitting to Paypal.', 'woocommerce-other-payment-gateway' ),
			    ),
			    'hide_sku' => array(
			        'title' 		=> __( 'Hide SKU', 'woocommerce-other-payment-gateway' ),
			        'type' 			=> 'checkbox',
			        'default' 		=> 'yes',
			        'description' 	=> __( 'Hide the SKU of the product when submitting to Paypal.', 'woocommerce-other-payment-gateway' ),
			    ),
			    'allow_countries' => array(
			        'title' => __( 'Allow Countries', 'infipay-multi-paypal-checkout-payment-gateway' ),
			        'type' => 'text',
			        'default' => "US",
			        'description' 	=> __( 'Only customers from these countries are allowed to check out using this plugin. Enter <a target="_blank" href=\'https://www.nationsonline.org/oneworld/country_code_list.htm\'>country_code</a> (Alpha 2) separated by commas. Leaving blank is unlimited.', 'infipay-multi-paypal-checkout-payment-gateway' ),
			    ),
			    'order_button_text' => array(
			        'title' => __( 'Order Button Text', 'infipay-multi-paypal-checkout-payment-gateway' ),
			        'type' => 'text',
			        'default' => 'Proceed to Paypal',
			        'description' 	=> __( 'Set if the place order button should be renamed on selection.', 'infipay-multi-paypal-checkout-payment-gateway' ),
			    ),			    
		 );
	}
	/**
	 * Admin Panel Options
	 * - Options for bits like 'title' and availability on a country-by-country basis
	 *
	 * @since 1.0.0
	 * @return void
	 */
	public function admin_options() {
		?>
		<h3><?php _e( 'Infipay Multi Paypal Checkout Payment Settings', 'infipay-multi-paypal-checkout-payment-gateway' ); ?></h3>
			<div id="poststuff">
				<div id="post-body" class="metabox-holder columns-1">
					<div id="post-body-content">
						<table class="form-table">
							<?php $this->generate_settings_html();?>
						</table><!--/.form-table-->
					</div>
				</div>
				</div>
				<div class="clear"></div>
				<style type="text/css">
				.wpruby_button{
					background-color:#4CAF50 !important;
					border-color:#4CAF50 !important;
					color:#ffffff !important;
					width:100%;
					text-align:center;
					height:35px !important;
					font-size:12pt !important;
				}
                .wpruby_button .dashicons {
                    padding-top: 5px;
                }
				</style>
				<?php
	}

	public function validate_fields() {
        return true;

// 	    $textbox_value = (isset($_POST['multi_paypal_checkout_payment-admin-note']))? trim($_POST['multi_paypal_checkout_payment-admin-note']): '';
// 		if($textbox_value === ''){
// 			wc_add_notice( __('Please, complete the payment information.','woocommerce-custom-payment-gateway'), 'error');
// 			return false;
//         }
// 		return true;
	}

	public function process_payment( $order_id ) {
		global $woocommerce;
		$order = new WC_Order( $order_id );
		// Mark as on-hold (we're awaiting the cheque)
		$order->update_status($this->order_status, __( 'Awaiting payment', 'infipay-multi-paypal-checkout-payment-gateway' ));
		// Reduce stock levels
		wc_reduce_stock_levels( $order_id );
		if(isset($_POST[ $this->id.'-admin-note']) && trim($_POST[ $this->id.'-admin-note'])!=''){
			$order->add_order_note(esc_html($_POST[ $this->id.'-admin-note']),1);
		}
		
		// Add note created by Multi Paypal Payment
		//$order->add_order_note("Order created via Multi Paypal Payment Plugin.");
		
// 		$order_number = $order->get_order_number();
		$shop_domain = $_SERVER['HTTP_HOST'];
		
		if(strpos($this->multi_paypal_checkout_payment_server_domain, "http") === false){
		    $this->multi_paypal_checkout_payment_server_domain = "https://" . $this->multi_paypal_checkout_payment_server_domain;
		}
		
		// TungPG Mod - Send order information to Tool
		$send_order_to_tool_url = $this->multi_paypal_checkout_payment_server_domain . "/index.php?r=multi-paypal-payment/create-new-order";
		
		if(!(strpos($send_order_to_tool_url, "http") === 0)){
		    $send_order_to_tool_url = "https://" . $send_order_to_tool_url;
		}
		
		// Add note to order - tool name
		$note = __("multi-paypal-payment-gateway");
		$order->add_order_note( $note );
		
		// Get buyer ip address
		$buyer_ip = $this->getIPAddress();
		
		// Get the Paypal Shop Domain and Paypal Account id
		$options = array(
		'http' => array(
		'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
		'method'  => 'POST',
		'content' => http_build_query([
    		'shop_domain' => $shop_domain,
    		'shop_order_id' => $order_id,
    		'allow_countries' => trim($this->allow_countries),
    		'buyer_ip' => $buyer_ip,
    		'sandbox_enabled' => trim($this->sandbox_enabled),
		])
		)
		);
		$context  = stream_context_create($options);
		$api_response = file_get_contents($send_order_to_tool_url, false, $context);
		
		$result_object = (object)json_decode( $api_response, true );
		
		if(isset($result_object->error)){
		    $error_message = $result_object->error;
		    if(empty($result_object->show_error_to_buyer)){
		        $error_message = "Sorry, an error occurred while trying to process your payment. Please try again.";
		    }
		    
		    error_log($error_message);
		    wc_add_notice( __( $error_message, 'infipay-multi-paypal-checkout-payment-gateway' ), 'error' );
		    return array(
		        'result'   => 'failure',
		    );
		}
		
		// TODO check lai address truoc khi submit len paypal de phong loi bi clear het cart, thu bat loi tra ve boi paypal va sau do redirect back lai trang web
		
		// Get the information value
		$shop_name = $result_object->shop_name;
		$payment_shop_domain = $result_object->payment_shop_domain;
		$paypal_account_id = $result_object->ppaccid;
		$app_order_id = $result_object->app_order_id;
		
		// Call to paypal shop to get the paypal redirect url
		$paypal_shop_payment_request_url = "https://" . $payment_shop_domain . "/icheckout/?paypal-checkout=request-payment";
		
		$options = array(
		    'http' => array(
		        'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
		        'method'  => 'POST',
		        'content' => http_build_query([
		            'main_shop_name' => $shop_name,
		            'ppaccid' => $paypal_account_id, // Paypal Account Id, // Paypal Account Id
		            'app_order_id' => $app_order_id,
		            'shop_order_id' => $order_id,
		            'order_json' => $result_object->order_json,
		            'invoice_id_prefix' => $this->invoice_id_prefix,
		            'hide_item_title' => $this->hide_item_title,
		            'hide_sku' => $this->hide_sku,
		        ])
		    )
		);
		$context  = stream_context_create($options);
		$api_response = file_get_contents($paypal_shop_payment_request_url, false, $context);
		
		$result_object = (object)json_decode( $api_response, true );
		
		if(isset($result_object->error)){
		    $error_message = $result_object->error;
		    if(empty($result_object->show_error_to_buyer)){
		        $error_message = "Sorry, an error occurred while trying to process your payment. Please try again.";
		    }
		    
		    error_log($error_message);
		    wc_add_notice( __( $error_message, 'infipay-multi-paypal-checkout-payment-gateway' ), 'error' );
		    return array(
		        'result'   => 'failure',
		    );
		}
		
		if(!isset($result_object->approval_url)){
		    error_log("Could get Paypal Approval URL!");
		    wc_add_notice( __( "Sorry, an error occurred while trying to process your payment. Please try again.", 'infipay-multi-paypal-checkout-payment-gateway' ), 'error' );
		    return array(
		        'result'   => 'failure',
		    );
		}
		
		// Remove cart
		//$woocommerce->cart->empty_cart();
		
		// Redirect user to paypal approval page
		$redirect_url = "https://" . $payment_shop_domain . "/icheckout/?paypal-checkout=pay&redirect_url=" . urlencode($result_object->approval_url);
		return array(
			'result' => 'success',
		    'redirect' => $redirect_url
		);
		// End - TungPG Mod
		
		// Return thankyou redirect
// 		return array(
// 			'result' => 'success',
// 			'redirect' => $this->get_return_url( $order )
// 		);	
	}
	
	function process_refund( $order_id, $amount = NULL, $reason = '' ) {
	    // Get order information
	    $refund_order_tool_url = "https://" . $this->multi_paypal_checkout_payment_server_domain . "/index.php?r=multi-paypal-payment/process-refund";
	    $shop_domain = $_SERVER['HTTP_HOST'];
	    
	    $options = array(
	        'http' => array(
	            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
	            'method'  => 'POST',
	            'content' => http_build_query([
	                'shop_domain' => $shop_domain,
	                'shop_order_id' => $order_id,
	                'amount' => $amount,
	                'reason' => $reason,
	            ])
	        )
	    );
	    $context  = stream_context_create($options);
	    $api_response = file_get_contents($refund_order_tool_url, false, $context);
	    
	    $result_object = (object)json_decode( $api_response, true );
	    
	    if(isset($result_object->error)){
	        throw new Exception( __( $result_object->error, 'infipay-multi-paypal-checkout-payment-gateway' ) );
	        return false;
	    }
	    
	    if(!empty($result_object->success)){
	        // Take note to order
	        $order = wc_get_order( $order_id );
	        
	        $note = __("multi-paypal-payment-gateway<br/>Refunded: " . wc_price($amount) . ".");
	        $order->add_order_note( $note );
	        
	        return true;
	    }
	    
	    return false;
	}

	public function payment_fields(){
	    ?>
		<fieldset>
			 <div style="margin-top: 3px;">
    			<p class="form-row form-row-wide">
                   <label for="<?php echo $this->id; ?>-admin-note"><?php echo ($this->description);?> <span class="required">*</span></label>
    			</p>						
			</div>
			<div class="clear"></div>
		</fieldset>
		<?php
	}
	
	/**
	 * Get real user IP address
	 * @return String
	 */
	public function getIPAddress() {
	    //whether ip is from the share internet
	    if(!empty($_SERVER['HTTP_CLIENT_IP'])) {
	        $ip = $_SERVER['HTTP_CLIENT_IP'];
	    }
	    //whether ip is from the proxy
	    elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
	        $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	    }
	    //whether ip is from the remote address
	    else{
	        $ip = $_SERVER['REMOTE_ADDR'];
	    }
	    return $ip;
	}  
}
