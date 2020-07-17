<?php namespace Riedayme\InstagramKit;

Class InstagramResourceUser
{

	public static function GetUserInfoByID($userid)
	{
		
		$url = 'https://www.instagram.com/graphql/query/?query_hash=7c16654f22c819fb63d1183034a5162f&variables={"user_id":"'.$userid.'","include_chaining":false,"include_reel":true,"include_suggested_users":false,"include_logged_out_extras":false,"include_highlight_reels":false}';

		$headers = array();
		$headers[] = "User-Agent: ". InstagramUserAgent::Get('Windows');

		$access = InstagramHelper::curl($url, false, $headers);

		$response = json_decode($access['body'],true);

		if ($response['status'] == 'ok') {

			/* Result Success on explore/graphql-query-user-info.json */	

			if ($response['data']['user'] != null) {

				$username = $response['data']['user']['reel']['owner']['username'];
				$photo = $response['data']['user']['reel']['owner']['profile_pic_url'];

				return [
					'status' => true,
					'response' => [
						'username' => $username,
						'photo' => $photo
					]		
				];

			}else{

				/* Response No User Found 
				{
				  "data": {
				    "viewer": null,
				    "user": null
				  },
				  "status": "ok"
				}
				*/

				return [
					'status' => false,
					'response' => 'No user Found'
				];				
			}		

		}else{

			return [
				'status' => false,
				'response' => $access['body']
			];

		}		
	}

	public static function GetUserInfoByFBToken($token)
	{

		$url = 'https://www.instagram.com/accounts/fb_profile/';
		$postdata = "accessToken={$token}";

		$headers = array();
		$headers[] = "X-Csrftoken: ".InstagramCSRF::GetCSRFByAPI();

		$access = InstagramHelper::curl($url , $postdata , $headers );

		$response = json_decode($access['body'],true);

		if ($response['status'] == 'ok') {

			/* Result Success on explore/instagram-accounts-fb_profile.json */

			$username = $response['igAccount']['username'];
			$photo = $response['igAccount']['profilePictureUrl'];		

			return [
				'status' => true,
				'response' => [
					'username' => $username,
					'photo' => $photo
				]				
			];		

		}else{

			/* Response Failed
			{
			  "message": "invalid_response",
			  "status": "fail"
			}
			*/

			return [
				'status' => false,
				'response' => $access['body']
			];

		}		
	}

	public static function GetFacebookID($token)
	{
		$access = InstagramHelper::curl("https://graph.facebook.com/me?fields=name,picture&access_token={$token}");

		$response = json_decode($access['body'],true);

		if (array_key_exists('error', $response)) {

			/* Response Failed
			{
			  "error": {
			    "message": "Invalid OAuth access token.",
			    "type": "OAuthException",
			    "code": 190,
			    "fbtrace_id": "A5WRD9huPkECrq2M88aYFxc"
			  }
			}			
			*/
			
			return [
				'status' => false,
				'response' => $response['error']['message']
			];
		}

		/* Result Success on explore/graph.facebook.com-me.json */

		$fbid = $response['id'];

		return [
			'status' => true,
			'response' => $fbid
		];
	}

	public static function GetCurrentUserInfoByAPI($cookie){

		$url = 'https://i.instagram.com/api/v1/accounts/current_user/';

		$useragent = InstagramUserAgent::Get('Android');

		$access = InstagramHelperAPI::curl($url, false , false, $cookie, $useragent);

		$response = json_decode($access['body'],true);

		if ($response['status'] == 'ok') {

			/* Result Success on explore/api-v1-accounts-current_user.json */

			$username = $response['user']['username'];
			$photo = $response['user']['profile_pic_url'];

			return [
				'status' => true,
				'response' => [
					'username' => $username,
					'photo' => $photo
				]		
			];

		}else{

			/* Response Failed
			{
			  "message": "login_required",
			  "error_title": "You've Been Logged Out",
			  "error_body": "Please log back in.",
			  "logout_reason": 2,
			  "status": "fail"
			}
			*/

			return [
				'status' => false,
				'response' => $response['message']
			];

		}		

	}

	public static function GetUserInfoByAPI($userid){

		$url = 'https://i.instagram.com/api/v1/users/' . $userid . '/info/';

		$useragent = InstagramUserAgent::Get('Android');

		$access = InstagramHelperAPI::curl($url, false , false, false, $useragent);

		$response = json_decode($access['body'],true);

		if ($response['status'] == 'ok') {

			/* Result Success on explore/api-v1-users-userid-info-nocookie.json */

			$username = $response['user']['username'];
			$photo = $response['user']['profile_pic_url'];

			return [
				'status' => true,
				'response' => [
					'username' => $username,
					'photo' => $photo
				]		
			];

		}else{

			return [
				'status' => false,
				'response' => 'No user Found'
			];

		}		

	}	

	public static function GetUsernameInfoByAPI($username,$cookie)
	{

		$url = 'https://i.instagram.com/api/v1/users/' . $username . '/usernameinfo';

		$useragent = InstagramUserAgent::Get('Android');

		$access = InstagramHelperAPI::curl($url, false , false, $cookie, $useragent);

		$response = json_decode($access['body'],true);

		if ($response['status'] == 'ok') {

			/* Result Success on explore/api-v1-users-username-usernameinfo.json */

			$userid = $response['user']['pk'];
			$photo = $response['user']['profile_pic_url'];

			return [
				'status' => true,
				'response' => [
					'userid' => $userid,
					'photo' => $photo
				]		
			];

		}else{

			/* Response Failed
			{
			  "message": "login_required",
			  "error_title": "You've Been Logged Out",
			  "error_body": "Please log back in.",
			  "logout_reason": 2,
			  "status": "fail"
			}
			*/

			return [
				'status' => false,
				'response' => $response['message']
			];

		}
	}

	public static function GetUserIdByAPI($cookie,$username)
	{

		$url = 'https://i.instagram.com/api/v1/users/' . $username . '/usernameinfo';

		$useragent = InstagramUserAgent::Get('Android');

		$access = InstagramHelperAPI::curl($url, false , false, $cookie, $useragent);

		$response = json_decode($access['body'],true);

		$userid = $response['user']['pk'];

		return $userid;
	}	

	public function GetUserIdByScraping($username)
	{
		$url      = "https://www.instagram.com/" . $username;
		$html     = file_get_contents($url);
		$arr      = explode('window._sharedData = ', $html);
		$arr      = explode(';</script>', $arr[1]);
		$obj      = json_decode($arr[0], true);
		$id       = $obj['entry_data']['ProfilePage'][0]['graphql']['user']['id'];

		return $id;
	}

	public static function GetUserIdByWeb($username){
		
		$url = 'https://www.instagram.com/'.$username.'/?__a=1';

		$useragent = InstagramUserAgent::Get('Windows');

		$access = InstagramHelper::curl($url, false , false, false, $useragent);

		$result = json_decode($access['body'],true);

		if(is_null($result)){
			return false;
		}

		return $result['graphql']['user']['id'];
	}

}