<?php namespace Riedayme\InstagramKit;

Class InstagramAuthFacebookAPI
{

	public static function Login($access_token)
	{

		$url = 'https://i.instagram.com/api/v1/fb/facebook_signup/';

		$guid = InstagramHelperAPI::generateUUID(true);

		$postdata = [
			'dryrun' 			  => false,
			'phone_id' 			  => $guid,
			'adid' 				  => InstagramHelperAPI::generateUUID(true),
			'device_id' 		  => InstagramHelperAPI::generateDeviceId(true),
			'waterfall_id' 		  => InstagramHelperAPI::generateUUID(true),
			'fb_access_token' 	  => $access_token,
		];

		$login = InstagramHelper::curl($url, $postdata , false , false, InstagramUserAgent::Get('Android'));

		$response = json_decode($login['body'],true);

		if ($response['status'] == 'ok') {

			$cookie = InstagramCookie::ReadCookie($login['header']);
			$csrftoken = InstagramCookie::GetCSRFCookie($cookie);

			$userid = $response['logged_in_user']['pk'];
			$username = $response['logged_in_user']['username'];			
			$photo = $response['logged_in_user']['profile_pic_url'];				

			return [
				'status' => true,
				'response' => [
					'userid' => $userid,
					'username' => $username, 
					'photo' => $photo,
					'cookie' => $cookie,
					'csrftoken' => $csrftoken
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