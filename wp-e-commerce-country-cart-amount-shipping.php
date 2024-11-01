<?php
/*
 Plugin Name: WP E-Commerce Shipping (Country, cart amounts)
 Plugin URI: http://www.leewillis.co.uk/wordpress-plugins/?utm_source=wordpress&utm_medium=www&utm_campaign=wp-e-commerce-country-cart-amount-shipping
 Description: Shipping Module For WP E-Commerce bases prices on country and value of cart
 Version: 1.2
 Author: Lee Willis
 Author URI: http://www.leewillis.co.uk/?utm_source=wordpress&utm_medium=www&utm_campaign=wp-e-commerce-country-cart-amount-shipping
*/

/*
 This program is free software; you can redistribute it and/or modify it under the terms of the GNU General Public License as published by the Free Software Foundation, version 2.

 This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 */

class ses_cca_shipping {

	var $internal_name;
	var $name;
	var $is_external;
	var $country_list;

	function ses_cca_shipping () {

		$this->internal_name = "ses_cca_shipping";
		$this->name = "Country / Cart Amount Shipping";
		$this->is_external = FALSE;
		$this->country_list = $this->ses_cca_get_countries();
		return true;
	}
	
	function ses_cca_get_countries() {

		global $wpdb, $table_prefix;

		$countries = $wpdb->get_results("SELECT `id`,`country`
		                                   FROM {$table_prefix}wpsc_currency_list
		                                  WHERE visible = '1'
		                               ORDER BY country ASC", ARRAY_A);
		
		if ($countries) {
			foreach ($countries as $country) {
				$results[$country['id']] = $country['country'];
			}
		}

		return $results;

	}

	function getName() {
		return $this->name;
	}
	
	function getInternalName() {
		return $this->internal_name;
	}
	
	function cart_total_withoutshipping() {

		global $wpsc_cart;  

		if (isset($wpsc_cart) && is_object($wpsc_cart)) {
			$total = $wpsc_cart->calculate_subtotal();
			$total -= $wpsc_cart->coupons_amount;
			if(wpsc_tax_isincluded() == false){
				$total += $wpsc_cart->calculate_total_tax();
			}
			return $total;
		} else {
			return 0;
		}
	}

	function hide_donate_link() {
		
		$blogurl = get_bloginfo('url');

		$options = get_option($this->getInternalName().'_options');
		if (isset($options['validated']) && $options['validated'] > time()) {
			return TRUE;
		}
		if (isset($options['validation_fail_check']) && $options['validation_fail_check'] > time()) {
			return FALSE;
		}

		$validation_url = "http://www.leewillis.co.uk/?action=ses_plugin_validate";
		$validation_url .= "&plugin=".get_class($this);
		$validation_url .= "&site=".urlencode($blogurl);

		$response = wp_remote_get($validation_url);
		if(is_wp_error($response)) {
			$options['validation_fail_check'] = time() + 86400;
			update_option($this->getInternalName().'_options', $options);
			return FALSE;
		}
		$response_code = $response['response']['code'];

		if ($response_code == '200') {
			$options['validated'] = time() + 2592000;
			update_option($this->getInternalName().'_options', $options);
			return TRUE;
		} else {
			$options['validation_fail_check'] = time() + 86400;
			update_option($this->getInternalName().'_options', $options);
			return FALSE;
		}
	}

	function show_layers_form() {

		$shipping = get_option($this->getInternalName().'_options');
		
		if (!isset($_GET['country_code']) || $_GET['country_code'] == "") {
			return $this->getForm();
		} else {
			$country_code = $_GET['country_code'];
		}

		echo "Configure rates for ";
		echo "shipping to ".$this->country_list[$country_code].".";
		echo "<br/><br/>";

		echo '<div id="ses-cca-layers">';
		echo '<input type="hidden" name="ses_cca_shipping_country_code" value="'.$country_code.'">';

		if (isset($shipping[$country_code]) && count($shipping[$country_code])) {
			$cartitemamounts = array_keys($shipping[$country_code]);
			foreach ($cartitemamounts as $cartitemamount) {
				echo 'Cart value is <input type="text" name="'.$this->getInternalName().'_cca[]" style="width: 50px;" size="8" value="'.htmlentities($cartitemamount).'"> or above, ';
				echo 'shipping: <input type="text" name="'.$this->getInternalName().'_rates[]" style="width: 50px;" size="8" value="'.htmlentities($shipping[$country_code][$cartitemamount]).'"><br/>';
			}
		} else {
			echo 'Cart value is <input type="text" name="'.$this->getInternalName().'_cca[]" style="width: 50px;" size="8" value="0"> or above, ';
			echo 'shipping: <input type="text" name="'.$this->getInternalName().'_rates[]" style="width: 50px;" size="8"><br/>';
		}
		echo '</div>';
		echo '<br/>';
		echo '<a id="ses-cca-newlayer">New Layer</a>';
		echo '<script type="text/javascript">
                        jQuery("td.gateway_settings div.inside div.submit").expire();
			jQuery("td.gateway_settings div.inside div.submit").livequery(function() { jQuery(this).show();});
		      </script>';

		exit();
		
	}

	function getForm() {

		if (isset($_POST['country_code']) && $_POST['country_code'] != "") {
			$output = show_layers_form($_POST['country_code']);
		} else {
			$output = '<tr><td>';
			if (!$this->hide_donate_link()) {
				$output .= '<div align="center"><div class="donate" style="background: rgb(255,247,124); padding: 5px; margin-right: 5px; margin-bottom: 5px; color: #000; text-align: center; border: 1px solid #333; border-radius: 10px; -moz-border-radius: 10px; -webkit-border-radius: 10px; width: 240px;">This plugin is provided free of charge. If you find it useful, you should<strong><br><a href="http://www.leewillis.co.uk/wordpress-plugins/">donate here</a></strong><br/><small><a target="_blank" href="http://www.leewillis.co.uk/hide-donations/">Hide this message</a></div></div><br/>';
			}
			$output .= "Pick a country to configure the cart item count layers:<br/><br/>";
			$output .= '<select id="ses-cca-select" name="country_code"><option value="">-- Choose --</option>';
			foreach ($this->country_list as $country_code => $country_desc) {
				$output .= '<option value="'.$country_code.'">'.htmlentities($country_desc).'</option>';
			}
			$output .= '
		        </select>
		        <script type="text/javascript">
                           jQuery("td.gateway_settings div.inside div.submit").expire();
			   jQuery("td.gateway_settings div.inside div.submit").livequery(function() { jQuery(this).hide("slow");});
                           jQuery("#ses-cca-select").change(function() {
		             jQuery.ajax( { url: "admin-ajax.php?action=ses-cca-layers&country_code="+jQuery(this).val(),
                                        success: function(data) { jQuery("td.gateway_settings table.form-table").html(data); }
                                          }
                                        ) });
                           jQuery("#ses-cca-newlayer").expire();
                           jQuery("#ses-cca-newlayer").livequery("click", function(event){
			       jQuery("#ses-cca-layers").append("Cart value is <input type=\"text\" name=\"'.$this->getInternalName().'_cca[]\" style=\"width: 50px;\" size=\"8\"> or above, shipping: <input type=\"text\" name=\"'.$this->getInternalName().'_rates[]\" style=\"width: 50px;\" size=\"8\"><br/>");});
		        </script></td></tr>
			';
		}
		return $output;
	}
	


	/* Use this function to store the settings submitted by the form above
	 * Submitted form data is in $_POST */

	function submit_form() {

		if (!isset($_POST[$this->getInternalName().'_country_code']) ||
		    $_POST[$this->getInternalName().'_country_code'] == "") {
			return FALSE; 
		}

		// Get current settings array
		$shipping = get_option($this->getInternalName().'_options');
		if (!$shipping) {
			unset($shipping);
		}

		$country_code = $_POST[$this->getInternalName().'_country_code'];
		$cartitemcounts = $_POST[$this->getInternalName().'_cca'];
		$rates = $_POST[$this->getInternalName().'_rates'];

		$new_shipping = Array();

		// Build submitted data into correct format
		for ($i = 0; $i < count($cartitemcounts); $i++) {
			// Ignore blank rates
			if ( isset ( $rates[$i] ) && $rates[$i] != "" ) {
				$new_shipping[$cartitemcounts[$i]] = $rates[$i];
			}
		}
		if ( $new_shipping) {
			krsort($new_shipping,SORT_NUMERIC);
		}

		$shipping[$country_code] = $new_shipping;
			
		update_option($this->getInternalName().'_options',$shipping);
	
		return true;

	}
	
	function get_item_shipping(&$cart_item) {

		global $wpdb;

		// If we're calculating a price based on a product, and that the store has shipping enabled

		$product_id = $cart_item->product_id;
		$quantity = $cart_item->quantity;
		$weight = $cart_item->weight;
		$unit_price = $cart_item->unit_price;

    		if (is_numeric($product_id) && (get_option('do_not_use_shipping') != 1)) {

			$country_code = $_SESSION['wpsc_delivery_country'];

			// Get product information
      			$product_list = $wpdb->get_row("SELECT *
			                                  FROM `".WPSC_TABLE_PRODUCT_LIST."`
				                         WHERE `id`='{$product_id}'
			                                 LIMIT 1",ARRAY_A);

       			// If the item has shipping enabled
      			if($product_list['no_shipping'] == 0) {

        			if($country_code == get_option('base_country')) {

					// Pick up the price from "Local Shipping Fee" on the product form
          				$additional_shipping = $product_list['pnp'];

				} else {

					// Pick up the price from "International Shipping Fee" on the product form
          				$additional_shipping = $product_list['international_pnp'];

				}          

				// Item shipping charges are per unit quantity
        			$shipping = $quantity * $additional_shipping;

			} else {

        			//if the item does not have shipping
        			$shipping = 0;

			}

		} else {

      			//if the item is invalid or store is set not to use shipping
			$shipping = 0;

		}

    		return $shipping;	
	}
	

	function getQuote() {

		global $wpdb, $wpsc_cart, $table_prefix;

		// Get the cart weight
		$cart_total = $this->cart_total_withoutshipping();

		// Get the layers for this country
		if (isset($_POST['country'])) {

			$country = $_POST['country'];
			$_SESSION['wpsc_delivery_country'] = $country;

		} else {

			$country = $_SESSION['wpsc_delivery_country'];

		}
		// Assume that it's an isocode, and try and look it up
		$sql = $wpdb->prepare("SELECT `id`
		                         FROM {$table_prefix}wpsc_currency_list
		                        WHERE isocode = %s", $country);
		$country_id = $wpdb->get_var($sql);

		if (!$country_id) {
			return Array();
		}

		$shipping = get_option($this->getInternalName().'_options');

		if (isset($shipping[$country_id]) && count($shipping[$country_id])) {
			$layers = $shipping[$country_id]; 
		} else {
			// No shipping layers configured for this country
			return Array();
		}
		
		// Note the layers are sorted before being saved into the options
		// Here we assume that they're in (descending) order
		foreach ($layers as $key => $shipping) {
			if ($cart_total >= (int)$key) {
				return array("Shipping"=>(float)$shipping);
			}
		}

		// We couldn't find a rate - exit out.
		return Array();
		
	}
	
	
} 

function ses_cca_shipping_add($wpsc_shipping_modules) {

	global $ses_cca_shipping;
	$ses_cca_shipping = new ses_cca_shipping();

	$wpsc_shipping_modules[$ses_cca_shipping->getInternalName()] = $ses_cca_shipping;

	return $wpsc_shipping_modules;
}
	
add_filter('wpsc_shipping_modules', 'ses_cca_shipping_add');
add_action('wp_ajax_ses-cca-layers',array(&$ses_cca_shipping,"show_layers_form"));

?>
