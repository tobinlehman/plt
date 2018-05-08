<?php

/*
 * 1ShoppingCart Shopping Cart Integration Functions
 * Original Author : Mike Lopez
 * Version: $Id: integration.shoppingcart.1shoppingcart.php 2935 2015-12-08 08:10:11Z mike $
 */

//information below is now loaded in integration.shoppingcarts.php
//$__classname__ = 'WLM_INTEGRATION_1SHOPPINGCART';
//$__optionname__ = 'scthankyou';
//$__methodname__ = 'OneShoppingCart';

if (!class_exists('WLM_INTEGRATION_1SHOPPINGCART')) {

	class WLM_INTEGRATION_1SHOPPINGCART {

		function OneShoppingCart($that) {
			if (in_array(strtolower(trim(wlm_arrval($_POST,'status'))), array('accepted', 'approved', 'authorized', 'pending'))) { //accept even PENDING, let checkstatus handle it later
//				add_filter('rewrite_rules_array',array(&$that,'RewriteRules'));
//				$GLOBALS['wp_rewrite']->flush_rules();
				if (!trim(wlm_arrval($_POST,'name')))
					$_POST['name'] = 'Firstname Lastname';
				$name = explode(' ', $_POST['name']);
				$_POST['lastname'] = array_pop($name);
				$_POST['firstname'] = implode(' ', $name);
				$_POST['action'] = 'wpm_register';
				$_POST['wpm_id'] = $_POST['sku1'];
				$_POST['username'] = $_POST['email1'];
				$orig_email = $_POST['email'] = $_POST['email1'];
				$_POST['password1'] = $_POST['password2'] = $that->PassGen();

				$address = array();
				$address['company'] = $_POST['shipCompany'];
				$address['address1'] = $_POST['shipAddress1'];
				$address['address2'] = $_POST['shipAddress2'];
				$address['city'] = $_POST['shipCity'];
				$address['state'] = $_POST['shipState'];
				$address['zip'] = $_POST['shipZip'];
				$address['country'] = $_POST['shipCountry'];

				$_POST['sctxnid'] = $_POST['orderID'];

				$_POST['wpm_useraddress'] = $address;


				//cache the order
				$onescmerchantid = trim($that->GetOption('onescmerchantid'));
				$onescapikey = trim($that->GetOption('onescapikey'));
				if ($onescmerchantid && $onescapikey) {
					require_once($that->pluginDir . '/extlib/OneShopAPI.php');
					require_once($that->pluginDir . '/extlib/WLMOneShopAPI.php');
					$api = new WLMOneShopAPI($onescmerchantid, $onescapikey, 'https://www.mcssl.com');
					$order = $api->get_order_by_id($_POST['orderID'], true);
				}

				// support 1SC upsells
				if (trim($that->GetOption('onesc_include_upsells'))) {
					if (count($order['upsells'])) {
						$_POST['additional_levels'] = $order['upsells'];
					}
				}

				$that->ShoppingCartRegistration();
			} else {
				$onescmerchantid = trim($that->GetOption('onescmerchantid'));
				$onescapikey = trim($that->GetOption('onescapikey'));

				if ($onescmerchantid && $onescapikey) {
					$raw_post_data = file_get_contents('php://input');
					require_once($that->pluginDir . '/extlib/OneShopAPI.php');
					$API = new OneShopAPI($that->GetOption('onescmerchantid'), $that->GetOption('onescapikey'), 'https://www.mcssl.com');

					$requestBodyXML = new DOMDocument();

					# Load the request body into XML and check that the result has been parsed into XML
					
					//error_log('raw_post_data');
					//error_log(print_r($raw_post_data,1));
					//error_log('API');
					//error_log(print_r($API,1));
					
					if($raw_post_data!=''){
						if ($requestBodyXML->loadXML($raw_post_data) == true )  {
							$notificationType = $requestBodyXML->documentElement->nodeName;
							$tokenNode = $requestBodyXML->getElementsByTagName('Token')->item(0)->nodeValue;

							switch (strtolower($notificationType)) {
								case "neworder":
									$apiResult = $API->GetOrderById($tokenNode);
									break;

								default:
									# May have other types of notifications in the future
									break;
							}

							$apiResultXML = new DOMDocument();

							if ($apiResultXML->loadXML($apiResult) == true) {
								# Check if the API returned an error
								$apiSuccess = $apiResultXML->getElementsByTagName('Response')->item(0)->getAttribute('success');
								if ($apiSuccess == 'true') {

									$orderXML = &$apiResultXML;
									$status = strtolower($orderXML->getElementsByTagName('OrderStatusType')->item(0)->nodeValue);

									$_POST['sctxnid'] = $orderXML->getElementsByTagName('OrderId')->item(0)->nodeValue;
									if (in_array($status, array('accepted', 'approved', 'authorized','pending'))) {
										$that->ShoppingCartReactivate();

										// Add hook for Shoppingcart reactivate so that other plugins can hook into this
										$_POST['sc_type'] = '1ShoppingCart';
										do_action('wlm_shoppingcart_rebill', $_POST);

									} else {
										$that->ShoppingCartDeactivate();
									}
								}
							}
						}
					}
				}
			}
		}
	}
}
