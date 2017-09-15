jQuery(document).ready(function() {
	if(jQuery('#payment_method_stripe_subscription').attr('checked')){
		jQuery('#place_order').val('Pay vai stripe');
	}	
	jQuery('body').on('click','input[name="payment_method"]',function(){
		if(jQuery(this).val()=='stripe_subscription'){			
			jQuery('#place_order').val('Pay vai stripe');		
		}
		else{
			jQuery('#place_order').val('Place order');
		}
	});

	jQuery('body').on('click','#place_order',function(e){
		if(jQuery(this).val()=='Pay vai stripe'){
			e.preventDefault();
			var form=jQuery(this).parent('form');
			var billing_email=jQuery('#billing_email').val();
			var price=jQuery('#stripe_data').attr('data-amount');
			var charge_currency=jQuery('#stripe_data').attr('data-currency');
			var site_icon=jQuery('#stripe_data').attr('data-image');
			var site_name=jQuery('#stripe_data').attr('data-name');			
			var access_key=jQuery('#stripe_data').attr('data-key');
			var handler = StripeCheckout.configure({
			    key: access_key,
			    token: function(token, args){
			    	jQuery('form.woocommerce-checkout').append( '<input type="hidden" class="stripe_token" name="stripe_token" value="' + token.id + '"/>' );					
			      	jQuery('form.woocommerce-checkout').submit();
			    }
			  });
			handler.open({
		      amount: price,
		      email:billing_email,
		      name: site_name,
		      image:site_icon,
		      description: "place the order",
		      panelLabel: "Pay",
		      currency: charge_currency,
		    });			
		}		
	});
});