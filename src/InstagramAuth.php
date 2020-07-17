<?php namespace Riedayme\InstagramKit;

Class InstagramAuth
{

	public static function Login($username,$password) 
	{

		$url = 'https://www.instagram.com/accounts/login/ajax/';

		$password_enc = '#PWD_INSTAGRAM_BROWSER:0:' . time() . ':' . $password;

		$postdata = "username={$username}&enc_password={$password_enc}&queryParams=%7B%7D&optIntoOneTap=false";

		$cookiedata = InstagramCSRF::GetCSRFBySharedData();

		$headers = array();
		$headers[] = 'Referer: https://www.instagram.com/accounts/emailsignup/';
		$headers[] = "User-Agent: ". InstagramUserAgent::Get('Windows');
		$headers[] = "X-Csrftoken: ".$cookiedata['csrftoken'];
		$headers[] = "Cookie: ". $cookiedata['all'];

		$login = InstagramHelper::curl($url, $postdata , $headers);

		$result = json_decode($login['body']);

		if (is_null($result)) {

			return [
				'status' => false,
				'response' => $login['body']
			];

		}

		if($result->authenticated == true)
		{

			/* Result Success on explore/accounts-login-ajax.json */

			$userid = $result->userId;

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

			/* Response error
			{
			  "user": true,
			  "authenticated": false,
			  "status": "ok"
			}
			*/

			if ($result->user == true) {

				return [
					'status' => false,
					'response' => 'Password Error'
				];

			}else {

				return [
					'status' => false,
					'response' => 'Username not found'
				];

			}

		}
	}
}