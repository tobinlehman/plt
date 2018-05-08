<?php
/**
 * Handles the polling of the 1ShoppingCart API
 * to check the status of 1ShoppingCart orders
 * and set level status accordingly
 */


/*
 * How this works:
 * - First, transaction ID format
 *   initially stored as the numeric transaction ID passed by 1SC
 *   ex: 12345678
 *   
 *   eventually updated to 1SC-OrderID-1SCProductID-1SCClientID
 *   ex: 1SC-12345678-45678-981234
 *   
 *   which is then eventually updated to 1SC-OrderID-1SCProductID-1SCClientID-ChildID
 *   ex: 1SC-12345678-45678-981234-23456789
 *   
 * - We have 3 WP Cron jobs
 * 1. wishlistmember_1shoppingcart_queue_orders
 *    runs daily at 10am
 *    calls QueueOrders()
 *    - queues existing transaction IDs for checking
 *    - gets a list of new orders from 1sc for checking
 *    - stores 1sc-orders-last-params in WLM's options table to keep track of our last request dates
 *    - if no last request dates are stored, then we pull everything starting from the date of the
 *      oldest order based on transaction IDs stored by WLM
 *
 * 2. wishlistmember_1shoppingcart_get_orders_detail
 *    runs every 15 minutes
 *    calls GetOrdersDetail()
 *    - updates the transaction ID from numerical format into 1SC-x-x-x format
 *    - gets order details from 1SC and queues it for later processing
 *
 * 3. wishlistmember_1shoppingcart_process_orders
 *    runs every 15 minutes
 *    calls ProcessOrders()
 *    - processes the orders and updates the status of membership levels accordingly
 *      - if there's a child ID then we check the status of the child ID
 *      - if there's no child ID then we check the status of the parent ID
 */

class WLM_INTEGRATION_1SHOPPINGCART_INIT {
	var $api;

	/**
	 * Constructor
	 */
	function __construct() {
		global $WishListMemberInstance;

		if ( isset( $WishListMemberInstance ) ) {
			// get 1sc api information
			$onescmerchantid = trim( $WishListMemberInstance->GetOption( 'onescmerchantid' ) );
			$onescapikey = trim( $WishListMemberInstance->GetOption( 'onescapikey' ) );
		}

		// bail if there is no or incomplete api information
		if ( !$onescmerchantid || !$onescapikey ) {
			return;
		}

		// load required libs
		require_once $WishListMemberInstance->pluginDir . '/extlib/OneShopAPI.php';
		require_once $WishListMemberInstance->pluginDir . '/extlib/WLMOneShopAPI.php';

		// initialize api
		$this->api = new WLMOneShopAPI( $onescmerchantid, $onescapikey, 'https://www.mcssl.com' );

		$this->merchantid = $onescmerchantid;
		$this->apikey = $onescapikey;

		// set wp cron if needed
		if ( !wp_next_scheduled( 'wishlistmember_1shoppingcart_queue_orders' ) ) {
			// wp cron to pull new orders from 1sc is set to run at 10am daily
			// queue existing and new orders for processing later
			$time = strtotime( '10am', strtotime( '10am', strtotime( '+1 day -10 hours' ) ) );
			wp_schedule_event( $time, 'daily', 'wishlistmember_1shoppingcart_queue_orders' );
		}

		// get order details
		if ( !wp_next_scheduled( 'wishlistmember_1shoppingcart_get_orders_detail' ) ) {
			wp_schedule_event( time(), 'everyfifteenminutes', 'wishlistmember_1shoppingcart_get_orders_detail' );
		}

		// process orders
		if ( !wp_next_scheduled( 'wishlistmember_1shoppingcart_process_orders' ) ) {
			wp_schedule_event( time(), 'everyfifteenminutes', 'wishlistmember_1shoppingcart_process_orders' );
		}

		// add action for our crons
		add_action( 'wishlistmember_1shoppingcart_queue_orders', array( $this, 'QueueOrders' ) );
		add_action( 'wishlistmember_1shoppingcart_get_orders_detail', array( $this, 'GetOrdersDetail' ) );
		add_action( 'wishlistmember_1shoppingcart_process_orders', array( $this, 'ProcessOrders' ) );
	}

	/**
	 * Simple 1SC Get API
	 *
	 * @param string  $request Request being made. Ex: ORDERS/LIST
	 * @param array   $params  Optional parameters to pass
	 * @param integer $limit   RecordSets to retrieve. Default is 1. Set to "0" to get all RecordSets
	 * @return array array of XML Records returned
	 */
	function SimpleAPI( $request, $params = array(), $limit = 1 ) {
		$request = trim( preg_replace( array( '#^/#', '#/$#' ), '', $request ) );
		$pattern = 'https://mcssl.com/API/%d/%s?key=%s';

		if ( empty( $this->merchantid ) || empty( $this->apikey ) || empty( $request ) ) {
			return '';
		}

		if ( !empty( $params ) ) {
			$params = '&' . http_build_query( $params );
		} else {
			$params = '';
		}

		$results = array();
		$read = 1;

		$base_url = sprintf( $pattern, $this->merchantid, $request, $this->apikey );

		while ( $read ) {
			$read = 0;
			$url = $base_url . $params;
			$result = wp_remote_retrieve_body( wp_remote_get( $url ) );
			if ( $result ) {
				$results[] = $result;
				if ( preg_match( '#<nextrecordset>(.+?)</nextrecordset>#im', $result, $matches ) ) {
					if ( preg_match_all( '/<([^\s]+?)>(.+?)</im', $matches[1], $matches ) ) {
						$params = '&' . http_build_query( array_combine( $matches[1], $matches[2] ) );
						$read = 1;
					}
				}
			}

			$limit--;

			if ( empty( $limit ) ) {
				$read = 0;
			}

		}

		return $results;
	}

	/**
	 * Get Order Details of Queued items from 1SC's API
	 * and queues the result for later processing
	 *
	 * Checks for the following queue names
	 * - 1sc-update-txn-id
	 * - 1sc-get-order-detail
	 *
	 */
	function GetOrdersDetail() {
		global $wpdb, $WishListMemberInstance;

		if ( get_transient( 'running-1sc-' . __FUNCTION__ ) ) {
			return;
		}
		set_time_limit( HOUR_IN_SECONDS / 4 );
		set_transient( 'running-1sc-' . __FUNCTION__, 1, HOUR_IN_SECONDS / 4 );

		$queue = new WishlistAPIQueue;

		$queue_names = array(
			'1sc-update-txn-id',
			'1sc-get-order-detail',	
		);

		foreach ( $queue_names as $queue_name ) {
			$queue_items = $queue->get_queue( $queue_name );
			if ( $queue_items ) {
				foreach ( $queue_items as $queue_item ) {
					$order = $queue_item->value;
					$result = $this->SimpleAPI( 'ORDERS/'.$order );
					if ( $result ) { // order found
						if ( preg_match( '#(\d+)</clientid>#im', $result[0], $match ) ) { // client id found
							$clientid = $match[1];
							if ( preg_match( '#(\d+)</productid>#im', $result[0], $match ) ) { // product id found
								$productid = $match[1];

								// update the transaction id
								$updated_order_id = sprintf( '1SC-%d-%d-%d', $order, $productid, $clientid );

								if ( $queue_name == '1sc-update-txn-id' ) {
									$wpdb->update( $WishListMemberInstance->Tables->userlevel_options, array( 'option_value' => $updated_order_id ), array( 'option_name' => 'transaction_id', 'option_value' => $order ) );
								}

								// queue the order data for later processing
								$data = serialize( array( $updated_order_id, $result[0] ) );
								$queue->add_queue( '1sc-process-order', $data, '', true );
							}
						}
					}
					$queue->delete_queue( $queue_item->ID );
				}
			}
		}

		delete_transient( 'running-1sc-' . __FUNCTION__ );

	}

	/**
	 * Process orders queued by GetOrdersDetail()
	 */
	function ProcessOrders() {
		global $wpdb, $WishListMemberInstance;
		if ( get_transient( 'running-1sc-' . __FUNCTION__ ) ) {
			return;
		}
		set_time_limit( HOUR_IN_SECONDS / 4 );
		set_transient( 'running-1sc-' . __FUNCTION__, 1, HOUR_IN_SECONDS / 4 );
		$queue = new WishlistAPIQueue;

		$queue_items = $queue->get_queue( '1sc-process-order' );
		if ( $queue_items ) {
			foreach ( $queue_items as $queue_item ) {
				list( $order_reference, $order_data ) = maybe_unserialize( $queue_item->value );
				$order_parts = explode( '-', $order_reference );

				$order_id = $order_parts[1];
				$product_id = $order_parts[2];
				$client_id = $order_parts[3];

				if ( $order_id && $product_id && $client_id ) {
					$order_like = '^1SC-[0-9]+-' . $product_id . '-' . $client_id . '.*$';
					$query = $wpdb->prepare( "SELECT `option_value` FROM `{$WishListMemberInstance->Tables->userlevel_options}` WHERE `option_name`='transaction_id' AND `option_value` REGEXP %s", $order_like );
					$txn_id = $wpdb->get_var( $query );
					if ( $txn_id ) {
						$id_to_check = $order_id;

						$txn_parts = explode( '-', $txn_id );
						$parent_id = $txn_parts[1];
						$child_id = @$txn_parts[4] + 0;

						if ( $order_id > $parent_id && $order_id > $child_id ) { // we have a new child
							// update transaction id
							$txn_parts[4] = $order_id;
							$child_id = $order_id;
							$wpdb->update( $WishListMemberInstance->Tables->userlevel_options, array( 'option_value' => implode( '-', $txn_parts ) ), array( 'option_name' => 'transaction_id', 'option_value' => $txn_id ) );
							$txn_id = implode( '-', $txn_parts );
						}

						if ( ( $order_id == $parent_id && !$child_id ) || $order_id == $child_id ) {
							if ( preg_match( '#([^<>]+)</orderstatustype>#im', $order_data, $status ) ) {
								$_POST['sctxnid'] = $txn_id;
								$status = strtolower( $status[1] );
								if ( in_array( $status, array( 'accepted', 'approved', 'authorized', 'pending' ) ) ) {
									$WishListMemberInstance->ShoppingCartReactivate();
								}
								if ( in_array( $status, array( 'error', 'declined', 'refunded', 'voided' ) ) ) {
									$WishListMemberInstance->ShoppingCartDeactivate();
								}
							}
						}
					}
				}
				$queue->delete_queue( $queue_item->ID );
			}
		}

		delete_transient( 'running-1sc-' . __FUNCTION__ );
	}

	/**
	 * Queue Orders for Processing
	 */
	function QueueOrders( ) {
		global $wpdb, $WishListMemberInstance;
		if ( get_transient( 'running-1sc-' . __FUNCTION__ ) ) {
			return;
		}
		set_time_limit( DAY_IN_SECONDS / 2 );
		set_transient( 'running-1sc-' . __FUNCTION__, 1, DAY_IN_SECONDS / 2 );

		$queue = new WishlistAPIQueue;

		// get orders from transaction IDs
		$transaction_ids = $wpdb->get_col( "SELECT DISTINCT `option_value` FROM `{$WishListMemberInstance->Tables->userlevel_options}` WHERE `option_name` = 'transaction_id' AND (`option_value` REGEXP '^[0-9]+$' OR `option_value` REGEXP '^1SC-[0-9]+-[0-9]+-[0-9]+.*$') ORDER BY `option_value` ASC" );

		// add each old transaction ID format to queue for reformatting
		$oldest_transaction_id = 9999999999;
		foreach ( $transaction_ids as $transaction_id ) {
			if ( preg_match( '/^[0-9]+$/', $transaction_id ) ) {
				if ( $transaction_id < $oldest_transaction_id ) $oldest_transaction_id = $transaction_id;
				$queue->add_queue( '1sc-update-txn-id', $transaction_id, '', true );
			} else {
				$transaction_id = explode( '-', $transaction_id );
				if ( $transaction_id[1] < $oldest_transaction_id ) $oldest_transaction_id = $transaction_id[1];
				$queue->add_queue( '1sc-get-order-detail', $transaction_id[1], '', true );
				if ( !empty( $transaction_id[4] ) ) {
					$queue->add_queue( '1sc-get-order-detail', $transaction_id[4], '', true );
				}
			}
		}

		// get new "orders" and grab order ids
		$params = $WishListMemberInstance->GetOption( '1sc-orders-last-params' );

		// x-men days of...
		$future = date( 'm/d/Y', strtotime( '+2 days' ) );
		$past = date( 'm/d/Y', strtotime( '-2 days' ) );

		// fix end date if needed
		if ( strtotime( @$params['LimitEndDate'] ) < 1 ) {
			$oldest_order_in_wlm = $this->SimpleAPI( '/ORDERS/' . $oldest_transaction_id );
			preg_match( '#<orderdate>(.+)?</orderdate>#im', $oldest_order_in_wlm[0], $match );
			$params['LimitEndDate'] = date( 'm/d/Y', strtotime( '-1 day', strtotime( $match[1] ) ) );
		}

		$params['LimitStartDate'] = $params['LimitEndDate'];
		$params['LimitEndDate'] = $future;
		if ( strtotime( $params['LimitStartDate'] ) >= ( floor( time()/86400 )*86400 ) ) {
			$params['LimitStartDate'] = $past;
		}

		$params = array_intersect_key( $params, array_flip( array( 'LimitStartDate', 'LimitEndDate' ) ) );

		$new_orders = implode( '', $this->SimpleAPI( '/ORDERS/LIST', $params, 1, '1sc-orders-last-params' ) );
		if ( preg_match_all( '#(\d+)</order>#im', $new_orders, $matches ) ) {
			$WishListMemberInstance->SaveOption( '1sc-orders-last-params', $params );
			foreach ( $matches[1] as $new_order ) {
				$queue->add_queue( '1sc-get-order-detail', $new_order, '', true );
			}
		}

		delete_transient( 'running-1sc-' . __FUNCTION__ );
	}
}

// load the thing
new WLM_INTEGRATION_1SHOPPINGCART_INIT();
