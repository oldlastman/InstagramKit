<?php namespace Riedayme\InstagramKit;

Class InstagramAuthAPI
{

	public function Login($username,$password)
	{

		$url = 'https://i.instagram.com/api/v1/accounts/login/';

		$guid = InstagramHelperAPI::generateUUID(true);

		$data = [
			'device_id'           => InstagramHelperAPI::generateDeviceId(md5($username.$password)),
			'guid'                => $guid,
			'phone_id'            => InstagramHelperAPI::generateUUID(true),
			'username'            => $username,
			'password'            => $password,
			'login_attempt_count' => '0',
		];

		$postdata = InstagramHelperAPI::generateSignature(json_encode($data));

		$headers = [
			'Connection: close',
			'Accept: */*',
			'Content-type: application/x-www-form-urlencoded; charset=UTF-8',
			'Cookie2: $Version=1',
			'Accept-Language: en-US',
		];

		$login = InstagramHelper::curl($url, $postdata , $headers , false, InstagramUserAgent::Get('Android'));

		$response = json_decode($login['body'],true);

		if($response['status'] == 'ok') {

			/* Response Success
			{
			  "logged_in_user": {
			    "pk": 9868652404,
			    "username": "relaxing.media",
			    "full_name": "Relaxing Media",
			    "is_private": false,
			    "profile_pic_url": "https://instagram.fcgk18-2.fna.fbcdn.net/v/t51.2885-19/s150x150/104307119_305434823951354_7692373176389732433_n.jpg?_nc_ht=instagram.fcgk18-2.fna.fbcdn.net\u0026_nc_cat=102\u0026_nc_ohc=as14Fa8pXF0AX9IrCPM\u0026oh=939ce24f1c0dcb23c71a537632de272e\u0026oe=5F3AAF27",
			    "profile_pic_id": "2332332807793181419_9868652404",
			    "is_verified": false,
			    "has_anonymous_profile_picture": false,
			    "can_boost_post": false,
			    "is_business": false,
			    "account_type": 1,
			    "professional_conversion_suggested_account_type": 2,
			    "is_call_to_action_enabled": null,
			    "can_see_organic_insights": false,
			    "show_insights_terms": false,
			    "total_igtv_videos": 0,
			    "reel_auto_archive": "on",
			    "has_placed_orders": false,
			    "allowed_commenter_type": "any",
			    "nametag": {
			      "mode": 0,
			      "gradient": 2,
			      "emoji": "\ud83d\ude00",
			      "selfie_sticker": 0
			    },
			    "is_using_unified_inbox_for_direct": false,
			    "interop_messaging_user_fbid": 118577952861452,
			    "can_see_primary_country_in_settings": false,
			    "account_badges": [
			      
			    ],
			    "allow_contacts_sync": true,
			    "phone_number": ""
			  },
			  "status": "ok"
			}
			*/

			$userid = $response['logged_in_user']['pk'];
			$rank_token = $userid.'_'.$guid;

			$cookie = InstagramCookie::ReadCookie($login['header']);
			$csrftoken = InstagramCookie::GetCSRFCookie($cookie);

			$userinfo = InstagramResourceUser::GetUserInfoByID($userid);

			if (!$userinfo['status']) {
				return [
					'status' => false,
					'response' => $userinfo['response']
				];
			}

			return [
				'status' => 'success',
				'response' => [
					'userid' => $userid,
					'username' => $userinfo['response']['username'], 
					'photo' => $userinfo['response']['photo'],
					'cookie' => $cookie,
					'csrftoken' => $csrftoken,
					'uuid' => $guid,
					'rank_token' => $rank_token
				]
			];

		}else{

			if ($response['error_type'] == 'bad_password') {

				/* Response Error Password
				{
				  "message": "The password you entered is incorrect. Please try again.",
				  "invalid_credentials": true,
				  "error_title": "Incorrect password for username",
				  "buttons": [
				    {
				      "title": "Try Again",
				      "action": "dismiss"
				    }
				  ],
				  "status": "fail",
				  "error_type": "bad_password"
				}			
				*/

				return [
					'status' => false,
					'response' => $response['message']
				];

			}elseif ($response['error_type'] == 'checkpoint_challenge_required') {

				/* Response Error Check Point 

				*/

				$cookie = InstagramCookie::ReadCookie($login['header']);

				return [
					'status' => 'checkpoint',
					'response' => [
						'url' => $response['challenge']['url'],
						'cookie' => $cookie,
						'csrftoken' => InstagramCookie::GetCSRFCookie($cookie),
						'guid' => $guid
					]
				];		

			}else{
				return [
					'status' => false,
					'response' => $login['body']
				];
			}

		}
	}

	public function CheckPointSend($postdata,$choice = 1)
	{
		$url = $postdata['url'];

		$sendpost = "choice={$choice}";

		$headers = [
			'Connection: keep-alive',
			'Proxy-Connection: keep-alive',
			'Accept-Language: en-US,en',
			'x-csrftoken: '.$postdata['csrftoken'],
			'x-instagram-ajax: 1',
			'Referer: '.$url,
			'x-requested-with: XMLHttpRequest',
			'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
		];

		$access = InstagramHelperAPI::curl($url, $sendpost , $headers , $postdata['cookie'], InstagramUserAgent::Get('Android'));

		$response = json_decode($access['body'],true);

		if ($response['status'] == 'ok') {
			return [
				'status' => true,
				'response' => $response['extraData']['content'][1]['text']
			];
		}else{


			if (isset($response['message'])) {
				$message = $response['message'];
			}else{
				$message = $response['challenge']['errors'][0];
			}

			return [
				'status' => false,
				'response' => $message
			];
		}
	}

	public function CheckPointSolve($postdata)
	{
		$url = $postdata['url'];

		$sendpost = "security_code={$postdata['security_code']}";

		$headers = [
			'Connection: keep-alive',
			'Proxy-Connection: keep-alive',
			'Accept-Language: en-US,en',
			'x-csrftoken: '.$postdata['csrftoken'],
			'x-instagram-ajax: 1',
			'Referer: '.$url,
			'x-requested-with: XMLHttpRequest',
			'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
		];

		$access = InstagramHelperAPI::curl($url, $sendpost , $headers , $postdata['cookie'], InstagramUserAgent::Get('Android'));

		$response = json_decode($access['body'],true);

		if($response['status'] == 'ok') {

			$cookie = InstagramCookie::ReadCookie($access['header']);

			echo $cookie.PHP_EOL;

			$check_cookie = InstagramChecker::CheckLiveCookie($cookie);
			if (!$check_cookie['status']) die("[ERROR] cookie tidak bisa digunakan".PHP_EOL);

			$csrftoken = InstagramCookie::GetCSRFCookie($cookie);

			$rank_token = $check_cookie['response']['userid'].'_'.$postdata['guid'];

			return [
				'status' => true,
				'response' => [
					'userid' => $check_cookie['response']['userid'],
					'username' => $check_cookie['response']['username'], 
					'photo' => $check_cookie['response']['photo'],
					'cookie' => $cookie,
					'csrftoken' => $csrftoken,
					'uuid' =>$postdata['guid'],
					'rank_token' => $rank_token
				]
			];
		}else{
			return [
				'status' => false,
				'response' => $response['challenge']['errors'][0]
			];
		}
	}	

}