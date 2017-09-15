<?php
	/*
	Plugin Name: Woocomerce Stripe Subscription Payment Gatway
	Description: This plugin allows user to made the payment using stripe payment gateway.
	Version: 1.0
	Author: Coral Web Designs
	Author URI: http://coralwebdesigns.com/
	*/
	require('src/init.php');
	if ( in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
		add_action( 'plugins_loaded', 'init_stripe_gateway_class' );
		function init_stripe_gateway_class() {
			class WC_Stripe_Gateway extends WC_Payment_Gateway {
				public function __construct() {
					$this->id = 'stripe_subscription';
		      		$this->has_fields = true;
		      		$this->icon = plugins_url('woocommerce-stripe/src/img/stripe.png');
		      		$this->method_title = __('Stripe Subscription', 'woocommerce');
		      		$this->version = "1.0.0";
		      		$this->api_version = "1.0";
		      		$this->supports = array('subscriptions', 'products', 'refunds', 'subscription_cancellation', 'subscription_reactivation', 'subscription_suspension', 'subscription_amount_changes', 'subscription_payment_method_change', 'subscription_date_changes');
		      		$this->title = get_option('woocommerce_stripe_subscription_settings')['subscription_title'];
		      		$this->description = get_option('woocommerce_stripe_subscription_settings')['subscription_description'];
		      		$this->init_stripe_form_fields();
				    $this->init_settings();

				    add_action('woocommerce_update_options_payment_gateways', array(&$this, 'process_admin_options')); // < 2.0
		      		add_action('woocommerce_update_options_payment_gateways_' . $this->id, array($this, 'process_admin_options'));
	      		}
		      	function init_stripe_form_fields()
			    {
			      $this->form_fields = array(
			        'enabled' => array(
			          'title' => __('Enable/Disable', 'woocommerce'),
			          'type' => 'checkbox',
			          'label' => __('Enable Stripe Subscription', 'woocommerce'),
			          'default' => 'yes'
			        ),			        
			        'stripe_secrete_key' => array(
			          'title' => __('Stripe Secret Key', 'woocommerce'),
			          'type' => 'text',
			          'description' => 'The Gateway Authentication Secret Key',
			          'default' => 'stripe secrete key'
			        ),
			        'stripe_publishable_key' => array(
			          'title' => __("Stripe Publishable Key", 'woocommerce'),
			          'type' => 'text',
			          'description' => __("The Gateway Authentication Secret Key", "woocommerce"),
			          'default' => "stripe publishable key"
			        ),
			        'subscription_title' => array(
			          'title' => __("Gateway Name", "woocommerce"),
			          'type' => 'text',
			          'description' => __("The Gateway Name", "woocommerce"),
			          'default' => "Stripe Subscription"
			        ),
			        'subscription_description' => array(
			          'title' => __("Gateway Description", "woocommerce"),
			          'type' => "textarea",
			          'description' => __("The Gateway Description", "woocommerce"),
			          'default' => "Stripe Subscription payment method"
			        )
			      );
			    }
			    function process_payment( $order_id ) {
			    	$order = wc_get_order( $order_id );
			    	$stripe_token=$_POST['stripe_token'];
			    	$amount=$order->get_total()*100;
			    	\Stripe\Stripe::setApiKey(get_option('woocommerce_stripe_subscription_settings')['stripe_secrete_key']);			    	
			    	\Stripe\Charge::create(array(
					  "amount" => $amount,
					  "currency" => get_option('woocommerce_currency'),
					  "source" => $stripe_token,
					  "description" => "Charge for purchasing pruducts"
					));
			        $order->update_status( 'processing', __( 'Payment complete through stripe', 'wc-gateway-offline' ) );
			        $order->reduce_order_stock();
			        WC()->cart->empty_cart();
			        return array(
				        'result'    => 'success',
				        'redirect'  => $this->get_return_url( $order )
				    );
				}
				function process_refund($order_id){
					
				}
			}
			function add_stripe_gateway_class( $methods ) {
				$methods[] = 'WC_Stripe_Gateway';
				return $methods;
			}
			add_filter( 'woocommerce_payment_gateways', 'add_stripe_gateway_class' );

			add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'add_stripe_action_links' );
			function add_stripe_action_links( $links ) {
				$setting_link = get_stripe_setting_link();
				$plugin_links = array(
					'<a href="' . $setting_link . '">' . __( 'Settings', 'woocommerce-gateway-stripe' ) . '</a>',					
				);
				return array_merge( $plugin_links, $links );
			}
			function get_stripe_setting_link() {
				return admin_url( 'admin.php?page=wc-settings&tab=checkout&section=stripe_subscription');
			}
		}
		add_action( 'wp_enqueue_scripts', 'woo_stripe_script' );
		function woo_stripe_script(){
			if(is_checkout()){
				wp_enqueue_script( 'woo-stripe-checkout', 'https://checkout.stripe.com/v2/checkout.js', false );				
				wp_enqueue_script('woo-stripe-custom-js',plugins_url('woocommerce-stripe/src/js/stripe-custom-js.js'),false);
			}
		}
		add_action('woocommerce_review_order_before_submit','woo_stripe_credential');
		function woo_stripe_credential(){
			global $woocommerce;
			$total=$woocommerce->cart->get_cart_total();
			$total=explode(get_woocommerce_currency_symbol(), $total);
			$total_amount=explode(".", $total[1]);
			$total=$total_amount[0].$total_amount[1];
			?>
			<input type="hidden" id="stripe_data" data-name="<?php echo get_bloginfo('name'); ?>" data-image="<?php echo get_site_icon_url(); ?>" name="stripe_data" data-currency="<?php echo get_option('woocommerce_currency'); ?>" data-amount="<?php echo $total; ?>" data-key="<?php echo get_option('woocommerce_stripe_subscription_settings')['stripe_publishable_key'];?>">
			<?php
		}
	} else {
	  return;
	}
?>
