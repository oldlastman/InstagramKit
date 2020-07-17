<?php namespace Riedayme\InstagramKit;

Class InstagramAuthFacebook
{

	public static function Login($access_token)
	{

		$fbid = InstagramResourceUser::GetFacebookID($access_token);

		if (!$fbid['status']) {
			return [
				'status' => false,
				'response' => $fbid['response']
			];
		}

		$userinfo = InstagramResourceUser::GetUserInfoByFBToken($access_token);		

		if (!$userinfo['status']) {
			return [
				'status' => false,
				'response' => $userinfo['response']
			];
		}

		$url = 'https://www.instagram.com/accounts/login/ajax/facebook/';

		$postdata = "accessToken={$access_token}&fbUserId={$fbid['response']}&queryParams=%7B%7D";

		$cookiedata = InstagramCSRF::GetCSRFBySharedData();

		$headers = array();
		$headers[] = "X-Csrftoken: ".$cookiedata['csrftoken'];
		$headers[] = "Cookie: ". $cookiedata['all'];

		$login = InstagramHelper::curl($url,$postdata,$headers);

		$response = json_decode($login['body'],true);

		if ($response['status'] == 'ok') {

			/* Result Success on explore/account-login-ajax-facebook.json */

			$cookie = InstagramCookie::ReadCookie($login['header']);
			$csrftoken = InstagramCookie::GetCSRFCookie($cookie);

			$userid = $response['userId'];		

			return [
				'status' => true,
				'response' => [
					'userid' => $userid,
					'username' => $userinfo['response']['username'], 
					'photo' => $userinfo['response']['photo'],
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