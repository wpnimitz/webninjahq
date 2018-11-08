<?php


if ( class_exists( 'WooCommerce' ) ) {
	// ------------------
	// 1. Register new endpoint to use for My Account page
	// Note: Resave Permalinks or it will give 404 error
	 
	function wn_premium_support_endpoint() {
	    add_rewrite_endpoint( 'premium-support', EP_ROOT | EP_PAGES );
	}
	 
	add_action( 'init', 'wn_premium_support_endpoint' );
	 
	 
	// ------------------
	// 2. Add new query var
	 
	function wn_premium_support_query_vars( $vars ) {
	    $vars[] = 'premium-support';
	    return $vars;
	}
	 
	add_filter( 'query_vars', 'wn_premium_support_query_vars');
	 
	 
	// ------------------
	// 3. Insert the new endpoint into the My Account menu
	function add_premium_support_link( $items ) {
	    $items = array(
			'dashboard'          => __( 'Dashboard', 'woocommerce' ),
			'premium-support'    => __( 'Premium Support', 'woocommerce' ),
			'orders'             => __( 'Orders', 'woocommerce' ),
			'edit-account'       => __( 'Profile', 'woocommerce' ),
			'edit-address'       => __( 'Billing Information', 'woocommerce' ),
			'payment-methods'    => __( 'Payment Methods', 'woocommerce' ),
			'customer-logout'    => __( 'Logout', 'woocommerce' ),		
	    );

	    return $items;
	}
	 
	 
	add_filter( 'woocommerce_account_menu_items', 'add_premium_support_link');


	 
	 
	// ------------------
	// 4. Add content to the new endpoint
	 
	function premium_support_greetings() {
	echo '<h3>Premium Support</h3>
			<p style="margin-bottom: 20px;">Our Web Ninja HQ team brings years of experience and expertise ensuring you get quality result from start to finish. We know how important it is to stay in touch and provide access for when you have questions about your site progress and ongoing maintenance requirements, this is why customer service is the cornerstone of our business.</p>';
	echo do_shortcode( ' [ninja_form id=2] ' );
	}
	 
	add_action( 'woocommerce_account_premium-support_endpoint', 'premium_support_greetings' );



	// ------------------
	// 5. Add title to the new endpoint
	add_filter( 'woocommerce_endpoint_bbloomer_premium_support_title', 'wn_premium_support_title' );
	function wn_premium_support_title( $title ){
	    return 'Premium Support';
	}


	/******************************************
	* SHOW ALL ROOMS IN FIELD WITH KEY "ROOMS"
	******************************************/
	add_filter( 'ninja_forms_render_options', function($options,$settings){
	   if( $settings['key'] == 'selected_domain' ){

			$customer_orders = get_posts( array(
			    'numberposts' => -1,
			    'meta_key'    => '_customer_user',
			    'meta_value'  => get_current_user_id(),
			    'post_type'   => wc_get_order_types(),
			    'post_status' => array_keys( wc_get_order_statuses() ),
			) );

			echo "<pre>";
			print_r($customer_orders);
			echo "</pre>";

			if( count($customer_orders) >= 1 ) {
				for ($i=0; $i < count($customer_orders); $i++) { 
					$options[] = array('label' => get_post_meta($customer_orders[$i]->ID, 'domain_name', true), 'value' => $customer_orders[$i]->post_title);
				}
			}



	   }
	   return $options;
	},10,2);

	function wc_empty_cart_redirect_url() {
		return 'https://webninjahq.com/get-started/';
	}
	add_filter( 'woocommerce_return_to_shop_redirect', 'wc_empty_cart_redirect_url' );



	function woocommerce_maybe_add_multiple_products_to_cart( $url = false ) {
		// Make sure WC is installed, and add-to-cart qauery arg exists, and contains at least one comma.
		if ( ! class_exists( 'WC_Form_Handler' ) || empty( $_REQUEST['add-to-cart'] ) || false === strpos( $_REQUEST['add-to-cart'], ',' ) ) {
			return;
		}

		// Remove WooCommerce's hook, as it's useless (doesn't handle multiple products).
		remove_action( 'wp_loaded', array( 'WC_Form_Handler', 'add_to_cart_action' ), 20 );

		$product_ids = explode( ',', $_REQUEST['add-to-cart'] );
		$count       = count( $product_ids );
		$number      = 0;

		foreach ( $product_ids as $id_and_quantity ) {
			// Check for quantities defined in curie notation (<product_id>:<product_quantity>)
			// https://dsgnwrks.pro/snippets/woocommerce-allow-adding-multiple-products-to-the-cart-via-the-add-to-cart-query-string/#comment-12236
			$id_and_quantity = explode( ':', $id_and_quantity );
			$product_id = $id_and_quantity[0];

			$_REQUEST['quantity'] = ! empty( $id_and_quantity[1] ) ? absint( $id_and_quantity[1] ) : 1;

			if ( ++$number === $count ) {
				// Ok, final item, let's send it back to woocommerce's add_to_cart_action method for handling.
				$_REQUEST['add-to-cart'] = $product_id;

				return WC_Form_Handler::add_to_cart_action( $url );
			}

			$product_id        = apply_filters( 'woocommerce_add_to_cart_product_id', absint( $product_id ) );
			$was_added_to_cart = false;
			$adding_to_cart    = wc_get_product( $product_id );

			if ( ! $adding_to_cart ) {
				continue;
			}

			$add_to_cart_handler = apply_filters( 'woocommerce_add_to_cart_handler', $adding_to_cart->get_type(), $adding_to_cart );

			// Variable product handling
			if ( 'variable' === $add_to_cart_handler ) {
				woo_hack_invoke_private_method( 'WC_Form_Handler', 'add_to_cart_handler_variable', $product_id );

			// Grouped Products
			} elseif ( 'grouped' === $add_to_cart_handler ) {
				woo_hack_invoke_private_method( 'WC_Form_Handler', 'add_to_cart_handler_grouped', $product_id );

			// Custom Handler
			} elseif ( has_action( 'woocommerce_add_to_cart_handler_' . $add_to_cart_handler ) ){
				do_action( 'woocommerce_add_to_cart_handler_' . $add_to_cart_handler, $url );

			// Simple Products
			} else {
				woo_hack_invoke_private_method( 'WC_Form_Handler', 'add_to_cart_handler_simple', $product_id );
			}
		}
	}

	// Fire before the WC_Form_Handler::add_to_cart_action callback.
	add_action( 'wp_loaded', 'woocommerce_maybe_add_multiple_products_to_cart', 15 );


	/**
	 * Invoke class private method
	 *
	 * @since   0.1.0
	 *
	 * @param   string $class_name
	 * @param   string $methodName
	 *
	 * @return  mixed
	 */
	function woo_hack_invoke_private_method( $class_name, $methodName ) {
		if ( version_compare( phpversion(), '5.3', '<' ) ) {
			throw new Exception( 'PHP version does not support ReflectionClass::setAccessible()', __LINE__ );
		}

		$args = func_get_args();
		unset( $args[0], $args[1] );
		$reflection = new ReflectionClass( $class_name );
		$method = $reflection->getMethod( $methodName );
		$method->setAccessible( true );

		$args = array_merge( array( $class_name ), $args );
		return call_user_func_array( array( $method, 'invoke' ), $args );
	}



	add_filter( 'wc_add_to_cart_message', 'bbloomer_custom_add_to_cart_message' );
 
	function bbloomer_custom_add_to_cart_message() {
		$message    = "";
		return $message;
	}


	/*
	 * Display input on single product page
	 * @return html
	 */
	function kia_custom_option(){
	    $value = isset( $_POST['_custom_option'] ) ? sanitize_text_field( $_POST['_custom_option'] ) : '';
	    printf( '<label>%s</label><input name="_custom_option" value="%s" />', __( 'Enter your custom text', 'kia-plugin-textdomain' ), esc_attr( $value ) );
	}
	add_action( 'woocommerce_before_add_to_cart_button', 'kia_custom_option', 9 );

	/*
	 * Validate when adding to cart
	 * @param bool $passed
	 * @param int $product_id
	 * @param int $quantity
	 * @return bool
	 */
	function kia_add_to_cart_validation($passed, $product_id, $qty){

	    if( isset( $_POST['_custom_option'] ) && sanitize_text_field( $_POST['_custom_option'] ) == '' ){
	        $product = wc_get_product( $product_id );
	        wc_add_notice( sprintf( __( '%s cannot be added to the cart until you enter some custom text.', 'kia-plugin-textdomain' ), $product->get_title() ), 'error' );
	        return false;
	    }

	    return $passed;

	}
	add_filter( 'woocommerce_add_to_cart_validation', 'kia_add_to_cart_validation', 10, 3 );

	


	 /*
	 * Add custom data to the cart item
	 * @param array $cart_item
	 * @param int $product_id
	 * @return array
	 */
	function kia_add_cart_item_data( $cart_item, $product_id ){

	    if( isset( $_POST['_custom_option'] ) ) {
	        $cart_item['custom_option'] = sanitize_text_field( $_POST['_custom_option'] );
	    }

	    return $cart_item;

	}
	add_filter( 'woocommerce_add_cart_item_data', 'kia_add_cart_item_data', 10, 2 );





	/*
	 * Load cart data from session
	 * @param array $cart_item
	 * @param array $other_data
	 * @return array
	 */
	function kia_get_cart_item_from_session( $cart_item, $values ) {

	    if ( isset( $values['custom_option'] ) ){
	        $cart_item['custom_option'] = $values['custom_option'];
	    }

	    return $cart_item;

	}
	add_filter( 'woocommerce_get_cart_item_from_session', 'kia_get_cart_item_from_session', 20, 2 );

	/*
	 * Add meta to order item
	 * @param int $item_id
	 * @param array $values
	 * @return void
	 */
	function kia_add_order_item_meta( $item_id, $values ) {

	    if ( ! empty( $values['custom_option'] ) ) {
	        woocommerce_add_order_item_meta( $item_id, 'custom_option', $values['custom_option'] );           
	    }
	}
	add_action( 'woocommerce_add_order_item_meta', 'kia_add_order_item_meta', 10, 2 );


	/*
	 * Get item data to display in cart
	 * @param array $other_data
	 * @param array $cart_item
	 * @return array
	 */
	function kia_get_item_data( $other_data, $cart_item ) {

	    if ( isset( $cart_item['custom_option'] ) ){

	        $other_data[] = array(
	            'name' => __( 'Your custom text', 'kia-plugin-textdomain' ),
	            'value' => sanitize_text_field( $cart_item['custom_option'] )
	        );

	    }

	    return $other_data;

	}
	add_filter( 'woocommerce_get_item_data', 'kia_get_item_data', 10, 2 );

	/*
	 * Show custom field in order overview
	 * @param array $cart_item
	 * @param array $order_item
	 * @return array
	 */
	function kia_order_item_product( $cart_item, $order_item ){

	    if( isset( $order_item['custom_option'] ) ){
	        $cart_item_meta['custom_option'] = $order_item['custom_option'];
	    }

	    return $cart_item;

	}
	add_filter( 'woocommerce_order_item_product', 'kia_order_item_product', 10, 2 );


	/* 
	 * Add the field to order emails 
	 * @param array $keys 
	 * @return array 
	 */ 
	function kia_email_order_meta_fields( $fields ) { 
	    $fields['custom_field'] = __( 'Your custom text', 'kia-plugin-textdomain' ); 
	    return $fields; 
	} 
	add_filter('woocommerce_email_order_meta_fields', 'kia_email_order_meta_fields');

	/*
	 * Order Again
	 * @param array $cart_item
	 * @param array $order_item
	 * @param obj $order
	 * @return array
	 */
	function kia_order_again_cart_item_data( $cart_item, $order_item, $order ){

	    if( isset( $order_item['custom_option'] ) ){
	        $cart_item_meta['custom_option'] = $order_item['custom_option'];
	    }

	    return $cart_item;

	}
	add_filter( 'woocommerce_order_again_cart_item_data', 'kia_order_again_cart_item_data', 10, 3 );




}




// remove Order Notes from checkout field in Woocommerce
add_filter( 'woocommerce_checkout_fields' , 'alter_woocommerce_checkout_fields' );
function alter_woocommerce_checkout_fields( $fields ) {
    unset($fields['order']['order_comments']);
    return $fields;
}





add_filter( 'woocommerce_cart_ready_to_calc_shipping', 'disable_shipping_calc_on_cart', 99 );
function disable_shipping_calc_on_cart( $show_shipping ) {
    if( is_cart() ) {
        return false;
    }
    return $show_shipping;
}


function sv_remove_cart_product_link( $product_link, $cart_item, $cart_item_key ) {
    $product = apply_filters( 'woocommerce_cart_item_product', $cart_item['data'], $cart_item, $cart_item_key );
    return $product->get_title();
}
add_filter( 'woocommerce_cart_item_name', 'sv_remove_cart_product_link', 10, 3 );
add_filter( 'woocommerce_cart_item_thumbnail', '__return_false' );



function remove_item_from_cart() {
	$cart = WC()->instance()->cart;
	$id = $_POST['product_id'];
	$cart_id = $cart->generate_cart_id($id);
	$cart_item_id = $cart->find_product_in_cart($cart_id);

	if($cart_item_id){
	   $cart->set_quantity($cart_item_id, 0);
	   return true;
	} 
return false;
}

add_action('wp_ajax_remove_item_from_cart', 'remove_item_from_cart');
add_action('wp_ajax_nopriv_remove_item_from_cart', 'remove_item_from_cart');