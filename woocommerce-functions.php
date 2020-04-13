<?php

add_action( 'template_redirect', 'sx_woo_redirect_ty_page' );
 
function sx_woo_redirect_ty_page(){
	/* do nothing if we are not on the appropriate page */
	if( !is_wc_endpoint_url( 'order-received' ) || empty( $_GET['key'] ) ) {
		return;
	}
	$order_id = wc_get_order_id_by_order_key( $_GET['key'] );
	$order = wc_get_order( $order_id );
 	$key =$_GET['key'];
	if( $order ) {
        $url = get_permalink( 281 )."?order_id=$order_id&key=$key";
        wp_redirect($url);
		exit;
	}
}

function order_product_qty_func($atts) {
  $a = shortcode_atts( array(
      'orderid' => false,
  ), $atts );
  
ob_start();
  
 if( wc_get_order( $a['orderid'] ) ){
 
  // get the order using the orderid shortcode attribute
  $order = wc_get_order( $a['orderid'] );
  $total = 0;
  // loop over the order items
  $orderid = $a['orderid'];
 //echo print_r($order);
	echo "<p>Your Order number: <b>$orderid</b>" ;

  foreach ($order->get_items() as $item_id => $item_data) {
 
    // Get an instance of corresponding the WC_Product object
      $product = $item_data->get_product();
      $product_id = $product->get_id(); // Get the product id

      $title = get_the_title( $product_id );
      $total += $order->get_total();

		echo "<p class='text'>X 1 $title </p>";
  	}
	//echo $total;
 		echo "<h5 class='total'><span>Grand total</span> <span class='price'>$total<small>Taxes included.</small></span></h5>";
    // if the product id matches the current product id in the loop, return the qty
 }else{
  echo 'No order found';
 }
 
return ob_get_clean();
}
 
add_shortcode( 'ty_page', 'order_product_qty_func');


///////////////////////////////// Better display for checkout billing fields

add_filter( 'woocommerce_checkout_fields' , 'custom_checkout_billing_fields', 20, 1 );
function custom_checkout_billing_fields( $fields ){

    // Remove billing address 2
    unset($fields['billing']['billing_address_2']);

    if( 1 ){ // <== On cart page only
        // Change placeholder
        $fields['billing']['billing_phone']['placeholder']   = __( 'Phone number', 'hello' );
        $fields['billing']['billing_email']['placeholder']   = __( 'Email', 'hello' );
        $fields['billing']['billing_company']['placeholder'] = __( 'Company name (optional)', 'hello' );
        $fields['billing']['billing_first_name']['placeholder']   = __( 'First name', 'hello' );
        $fields['billing']['billing_last_name']['placeholder'] = __( 'Last name', 'hello' );

        // Change class
        $fields['billing']['billing_phone']['class']   = array('form-row-first'); //  50%
        $fields['billing']['billing_email']['class']   = array('form-row-last');  //  50%
        $fields['billing']['billing_state']['class']   = array('form-row-first');  //  50%
      	$fields['billing']['billing_postcode']['class']   = array('form-row-last');  //  50%
        $fields['billing']['billing_company']['class'] = array('form-row-wide');  // 100%
    }
    return $fields;
}
//add_filter('woocommerce_default_address_fields', 'custom_default_address_fields', 20, 1);
function custom_default_address_fields( $address_fields ){

    if( ! is_cart()){ // <== no cart page only
        // Change placeholder
       // $address_fields['first_name']['placeholder'] = __( 'First name', $domain );
       // $address_fields['last_name']['placeholder']  = __( 'Last name', $domain );
        //$address_fields['address_1']['placeholder']  = __( 'Adresse', $domain );
        //$address_fields['state']['placeholder']      = __( 'Stat', $domain );
        //$address_fields['postcode']['placeholder']   = __( 'ZIP code', $domain );
        //$address_fields['city']['placeholder']       = __( 'city', $domain );

        // Change class
        $address_fields['first_name']['class'] = array('form-row-first'); //  50%
        $address_fields['last_name']['class']  = array('form-row-last');  //  50%
        $address_fields['address_1']['class']  = array('form-row-wide');  // 100%
        $address_fields['state']['class']      = array('form-row-wide');  // 100%
        $address_fields['postcode']['class']   = array('form-row-first'); //  50%
        $address_fields['city']['class']       = array('form-row-last');  //  50%

    }
    return $address_fields;
}


// cart refresh when update_qty
add_action( 'wp_footer', 'bbloomer_cart_refresh_update_qty' ); 
function bbloomer_cart_refresh_update_qty() { 
   if (is_cart()) { 
      ?> 
      <script type="text/javascript"> 
         jQuery('div.woocommerce').on('click', 'input.qty', function(){ 
            jQuery("[name='update_cart']").trigger("click"); 
         }); 
      </script> 
      <?php 
   } 
}


//// woocommerce_cart_calculate_fees -- Adjustment to minimum order 
function prefix_add_discount_line( $cart ) {
	if($cart->subtotal > 100 ){
		return;
	}
  $minimum = 100 - $cart->subtotal;
  $cart->add_fee( __( 'Adjustment to minimum order (100$)', 'yourtext-domain' ) , +$minimum );
}
add_action( 'woocommerce_cart_calculate_fees', 'prefix_add_discount_line' );



//////////////////// Add ACF field to woo customer_completed_order email
add_action( 'woocommerce_email_order_details', 'bbloomer_add_content_specific_email', 10, 5 );
function bbloomer_add_content_specific_email( $order, $sent_to_admin, $plain_text, $email ) {
   if ( $email->id == 'customer_completed_order' ) {
		$tracking = get_field('tracking');
      if($tracking){
            echo '<h3 class="email-tracking-title">Tracking number</h3><p class="email-tracking">Your tracking number is: '.$tracking.'</p>
<p class="email-tracking">You can <a href="https://mypost.israelpost.co.il/itemtrace">track here</a></p>';
      }
   }
}
