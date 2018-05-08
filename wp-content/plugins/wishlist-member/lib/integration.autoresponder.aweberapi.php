<?php

/*
 * Generic Autoresponder Integration Functions
 * Original Author : Erwin Atuli
 * Version: $Id: integration.autoresponder.aweberapi.php 3007 2016-04-12 13:36:46Z mike $
 */

if (!class_exists('AWeberAPI')) {
	require_once dirname(__FILE__) . '/../extlib/aweber_api/aweber_api.php';
}

//$__classname__ = 'WLM_AUTORESPONDER_AWEBERAPI';
//$__optionname__ = 'aweberapi';
//$__methodname__ = 'AutoResponderAweberAPI';

if (!class_exists('WLM_AUTORESPONDER_AWEBERAPI')) {

	class WLM_AUTORESPONDER_AWEBERAPI {

		private $app_id = '2d8307c8';
		private $api_ver = '1.0';
		private $api_key = '';
		private $api_secret = '';
		private $auth_key = "";
		private $debug = false;
		private $wlm;

		/**
		 *
		 * @param $access_tokens list containing access_token & access_token_secret
		 */
		private $access_tokens = '';

		function set_wlm($wlm) {
			$this->wlm = $wlm;
		}

		function set_auth_key($auth_key) {
			$this->auth_key = $auth_key;
		}

		function get_authkey_url() {
			return sprintf("https://auth.aweber.com/%s/oauth/authorize_app/%s", $this->api_ver, $this->app_id);
		}

		/*
		 * Retreives current access tokens
		 * returns false if the access tokens are not usable
		 */

		function get_access_tokens() {
			$auth_key = $this->auth_key;

			if (empty($auth_key)) {
				return false;
			}
			/**
			 * @todo retrieve current access token from db
			 */
			$options = $this->wlm->GetOption('Autoresponders');
			$access_tokens = $options['aweberapi']['access_tokens'];
			if (empty($access_tokens)) {
				return false;
			}

			//test our access token
			$auth = $this->parse_authkey($auth_key);

			$api = new AWeberAPI($auth['api_key'], $auth['api_secret']);
			$api->adapter->debug = $this->debug;
			$api->user->tokenSecret = $auth['token_secret'];
			$api->user->requestToken = $auth['request_token'];
			$api->user->verifier = $auth['auth_verifier'];

			list($access_token, $access_token_secret) = $access_tokens;
			try {
				$account = $api->getAccount($access_token, $access_token_secret);
				return $access_tokens;
			} catch (Exception $e) {
				return false;
			}
		}

		function parse_authkey($key) {
			if (empty($key)) {
				return array();
			}
			list($api_key,
					$api_secret,
					$request_token,
					$token_secret,
					$auth_verifier) = explode('|', $key);

			$parsed = array(
				'api_key' => $api_key,
				'api_secret' => $api_secret,
				'request_token' => $request_token,
				'token_secret' => $token_secret,
				'auth_verifier' => $auth_verifier,
			);
			return $parsed;
		}

		/**
		 * Creates access tokens
		 */
		function renew_access_tokens() {
			$key = $this->auth_key;
			$auth = $this->parse_authkey($key);
			$api = new AWeberAPI($auth['api_key'], $auth['api_secret']);
			$api->adapter->debug = $this->debug;
			$api->user->tokenSecret = $auth['token_secret'];
			$api->user->requestToken = $auth['request_token'];
			$api->user->verifier = $auth['auth_verifier'];
			try {
				$access_tokens = $api->getAccessToken();
				return $access_tokens;
			} catch (Exception $e) {
				return false;
			}
		}

		function AweberProcessQueue($recnum = 10,$tries = 5){
			$WishlistAPIQueueInstance = new WishlistAPIQueue;
			$last_process = get_option("WLM_AweberAPI_LastProcess");
			$current_time = time();
			$tries = $tries > 1 ? (int)$tries:5;
			$error = false;
			//lets process every 10 seconds
			if(!$last_process || ($current_time - $last_process) > 10){
				$queues = $WishlistAPIQueueInstance->get_queue("aweber",$recnum,$tries,"tries,name");
				foreach($queues as $queue){
					$data = maybe_unserialize($queue->value);
					if($data['action'] == 'subscribe'){
						$params = array(
							'email' => $data['email'],
							'name' => $data['name'],
							'ip_address' => $data['ip_address']
						);
						$res = $this->subscribe($data['listID'], $params);
					}elseif($data['action'] == 'unsubscribe'){
						$res = $this->unsubscribe($data['aweber_uid'], $data['listID']);					
					}

					if(isset($res['error'])){
						$res['error'] = strip_tags($res['error']);
						$res['error'] = str_replace(array("\n", "\t", "\r"), '',$res['error']);
						$d = array(
							'notes'=> "{$res['code']}:{$res['error']}",
							'tries'=> $queue->tries + 1
							);
						$WishlistAPIQueueInstance->update_queue($queue->ID,$d);
						$error = true;
					}else{
						$WishlistAPIQueueInstance->delete_queue($queue->ID);
						$error = false;
					}
				}
				//save the last processing time when error has occured on last transaction				
				if($error){
					$current_time = time();
					if($last_process){
						update_option("WLM_AweberAPI_LastProcess",$current_time);
					}else{
						add_option("WLM_AweberAPI_LastProcess",$current_time);
					}
				}				
			}
		}

		function unsubscribe($aweber_uid, $list_id) {
			$access_tokens = $this->get_access_tokens();
			if (empty($access_tokens)) {
				// throw new Exception("Auth keys have already expired");
				error_log("WishList Member Aweber API Error: Auth keys have already expired");

				// If there's an error we log the error and put the member's info in the queue to be process later
				$WishlistAPIQueueInstance = new WishlistAPIQueue;
				$data = array(
					"action"=>"unsubscribe",
					"listID"=> $list_id,
					"aweber_uid"=>$aweber_uid,
					"update_existing"=>1,
					"replace_interests"=>false
				);				

				$qname = "aweberapi_" .time();
				$data = maybe_serialize($data);
				$WishlistAPIQueueInstance->add_queue($qname,$data,"For Queueing");

				return array('error' => 'Auth keys have already expired');
			}

			list($access_token, $access_token_secret) = $access_tokens;
			$key = $this->auth_key;
			$auth = $this->parse_authkey($key);
			$api = new AWeberAPI($auth['api_key'], $auth['api_secret']);
			$api->adapter->debug = $this->debug;
			$api->user->tokenSecret = $auth['token_secret'];
			$api->user->requestToken = $auth['request_token'];
			$api->user->verifier = $auth['auth_verifier'];

			try {
				$account = $api->getAccount($access_token, $access_token_secret);
				$list = $account->lists->getById($list_id);
				$subs = $list->subscribers;

				$sub = $subs->getById($aweber_uid);
				$res = $sub->delete();
			} catch (Exception $e) {
				error_log("An error occured while deleting: " . $e->getMessage());
				return false;
			}
		}

		function get_lists() {
			$access_tokens = $this->get_access_tokens();
			if (empty($access_tokens)) {
				throw new Exception("Auth keys have already expired");
			}

			list($access_token, $access_token_secret) = $access_tokens;
			$key = $this->auth_key;
			$auth = $this->parse_authkey($key);
			$api = new AWeberAPI($auth['api_key'], $auth['api_secret']);
			$api->adapter->debug = $this->debug;
			$api->user->tokenSecret = $auth['token_secret'];
			$api->user->requestToken = $auth['request_token'];
			$api->user->verifier = $auth['auth_verifier'];

			try {
				$account = $api->getAccount($access_token, $access_token_secret);
				$lists = array();
				foreach ($account->lists as $l) {
					$lists[] = $l->attrs();
				}
				return $lists;
			} catch (Exception $e) {
				error_log("An error occured while getting list: " . $e->getMessage());
				return false;
			}
		}

		/**
		 * Returns id of the subscriber
		 */
		function subscribe($list_id, $sub) {
			$key = $this->auth_key;
			$auth = $this->parse_authkey($key);
			
			if (empty($auth)) {
				// throw new Exception("Invalid Auth");
				error_log("WishList Member Aweber API Error: Invalid Auth");
				return false;
			}

			$access_tokens = $this->get_access_tokens();
			if (empty($access_tokens)) {
				// throw new Exception("Auth keys have already expired");
				error_log("WishList Member Aweber API Error: Auth keys have already expired");

				// If there's an error we log the error and put the member's info in the queue to be process later
				$WishlistAPIQueueInstance = new WishlistAPIQueue;
				$data = array(
					"action"=>"subscribe",
					"listID"=> $list_id,
					"email"=>$sub['email'],
					"name"=>$sub['name'],
					"ip_address"=>$_SERVER['REMOTE_ADDR'],
					"update_existing"=>1,
					"replace_interests"=>false
				);				

				$qname = "aweberapi_" .time();
				$data = maybe_serialize($data);
				$WishlistAPIQueueInstance->add_queue($qname,$data,"For Queueing");

				return array('error' => 'Auth keys have already expired');
			}

			list($access_token, $access_token_secret) = $access_tokens;
			$api = new AWeberAPI($auth['api_key'], $auth['api_secret']);
			$api->adapter->debug = $this->debug;
			$api->user->tokenSecret = $auth['token_secret'];
			$api->user->requestToken = $auth['request_token'];
			$api->user->verifier = $auth['auth_verifier'];

			try {
				$account = $api->getAccount($access_token, $access_token_secret);
				$list = $account->lists->getById($list_id);
				$subs = $list->subscribers;
				// now create a new subscriber
				$sub = $subs->create($sub);
				$attr = $sub->attrs();
				return $attr['id'];
			} catch (Exception $e) {
				error_log("An error occured while subscribing: " . $e->getMessage());
				return false;
			}
		}

		function AutoResponderAweberAPI($that, $ar, $wpm_id, $email, $unsub = false) {
			$this->set_wlm($that);
			$options = $this->wlm->GetOption('Autoresponders');
			$this->set_auth_key($options['aweberapi']['auth_key']);

			$list_id = $ar['connections'][$wpm_id];
			$autounsub = $ar['autounsub'][$wpm_id] == 'yes';


			if (empty($list_id)) {
				// exit if we don't have anything to sub/unsub to
				return;
			}

			$user = get_user_by_email($that->ARSender['email']);
			if ($unsub === false) {
				$params = array(
					'email' => $that->ARSender['email'],
					'name' => $that->ARSender['name'],
					'ip_address' => $_SERVER['REMOTE_ADDR']
				);

				$res = $this->subscribe($list_id, $params);
				//perist the user_id given by aweber
				if (!empty($res)) {
					add_user_meta($user->ID, 'integration.autoresponder.aweberapi.uid', $res);
				}
			} else {
				if (!$autounsub) {
					return;
				}
				$aweber_uid = get_user_meta($user->ID, 'integration.autoresponder.aweberapi.uid', true);
				$this->unsubscribe($aweber_uid, $list_id);
			}
		}

	}

}