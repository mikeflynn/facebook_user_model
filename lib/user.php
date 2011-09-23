<?php
class user
{
	private $login_url;
	private $logout_url;
	private $api_connection = false;

	public function __construct()
	{
		if(!defined('FACEBOOK_APP_ID') || !defined('FACEBOOK_API_SECRET'))
		{
			return false;
		}
	}

	public function get_category_rank($user_id, $category)
	{
		
	}

	public function get($where)
	{
		$gateway = new user_gateway();
		$data = $gateway->select($where, 1);
		if(!empty($data)) return new user_record($data);
		
		return false;
	}

	public function create($attributes)
	{
		return new user_record($attributes, true);
	}	
	
	public function login()
	{
		$facebook = $this->get_gateway();
		
		$fb_user = $facebook->getUser();
		
		$me = null;
		
		if(!empty($fb_user))
		{
			try {
		   		$uid = $facebook->getUser();
		    	$me = $facebook->api('/me');
		  	} catch (FacebookApiException $e) {
		    	error_log($e);
		  	}
		} 
		else
		{
			return false;
		}
		
		// login or logout url will be needed depending on current user state.
		if ($me)
		{			
			$this->set_logout_link($facebook->getLogoutUrl());
			
			$user_record = $this->get(
				array(
					'fb_id' => $me['id']
				)
			);

			if(!$user_record)
			{
				// Look to see if they have an account already in the system via the email.
				$user_record = $this->get(array('email'=>$me['email']));
				if(!$user_record)
				{
					// If they are new, create an account for them!	
					$user_data = array(
						'first_name'		=> $me['first_name'],
						'last_name'			=> $me['last_name'],
						'email'				=> $me['email'],
						'gender'			=> $me['gender'],
						'location'			=> ((!empty($me['location'])) ? $me['location']['name'] : ''),
						'photo'				=> "http://graph.facebook.com/{$me['id']}/picture",
						'birthday'			=> strtotime($me['birthday']),
						'fb_id'				=> $me['id'],
						'profile_url'		=> $me['link']
					);
					
					$user_record = $this->create($user_data);		
				}

				$user_record->save();
			}

			$this->set_session($user_record);
			
			return true;
		} 
		#else 
		#{
		#  	$this->login_url = $facebook->getLoginUrl();
		#}
		
		return false;
	}

	public function parse_signed_data($signed_request, $secret)
	{
		list($encoded_sig, $payload) = explode('.', $signed_request, 2);
		
		// Decode the data
		$sig = $this->base64_url_decode($encoded_sig);
		$data = json_decode($this->base64_url_decode($payload), true);
		
		if(strtoupper($data['algorithm']) !== 'HMAC-SHA256')
		{
			util::debug("Unknown algorithm presented.");
			return false; # Unknown algorithm presented.
		}
		
		// Check the signature
		$expected_sig = hash_hmac('sha256', $payload, $secret, $raw = true);
		if($sig !== $expected_sig) 
		{
			util::debug("Bad JSON signature!");
		    return false; # Bad JSON signature!
		}
		
		return $data;	
	}	
	
	public function get_info($facebook_uid)
	{
		return $this->get_gateway()->api('/'.$facebook_uid);
	}
	
	public function get_user_image($facebook_uid)
	{
		return $this->get_gateway()->api("/{$facebook_uid}/picture");
	}
	
	protected function base64_url_decode($input)
	{
    	return base64_decode(strtr($input, '-_', '+/'));
	}

	public function get_gateway()
	{
		if(empty($this->api_connection))
		{
			require_once(BASE_PATH.'/lib/ext/facebook/facebook.php');
			$this->api_connection = new Facebook(array(
  				'appId'  => FACEBOOK_APP_ID,
  				'secret' => FACEBOOK_API_SECRET,
  				'cookie' => true,
			));
		}
		
		return $this->api_connection;
	}				

	protected function set_logout_link($url)
	{
		if(!empty($url))
		{
			$this->logout_url = $url;
			$_SESSION['logout'] = $url;
		}
	}

	public function get_logout_link()
	{
		if(!empty($this->logout_url))
		{
			return $this->logout_url;
		}
	}

	public function get_login_link()
	{
		return $this->get_gateway()->getLoginUrl(array('scope'=>FACEBOOK_PERMISSIONS));
	}

	public function post_to_wall($message, $user='me')
	{
		if($_SESSION['last_wall_post'] < strtotime('+15 minutes'))
		{
			if(!is_array($message)){
				$message = array(
					'message' => $message,
				);
			}
			$message['access_token'] = $this->get_gateway()->getAccessToken();

			$_SESSION['last_wall_post'] = strtotime('now');
			
			return $this->get_gateway()->api(
				"/$user/feed/",
				'post',
				$message
			);
		}
	}

	protected function set_session(user_record $record)
	{
		if($record) $_SESSION['user'] = $record->to_array();
	}
	
	protected function get_logged_in_user()
	{
		if(!empty($_SESSION['user']))
		{
			return new user_record($_SESSION['user']);
		}
		
		return false;
	}

	protected function logout()
	{
		unset($_SESSION['user']);
	}
}
?>