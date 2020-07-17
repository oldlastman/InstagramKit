<?php namespace Riedayme\InstagramKit;

Class InstagramAuthCookie
{

	public static function Login($cookie)
	{

		$check_cookie = InstagramChecker::CheckLiveCookie($cookie);
		
		if (!$check_cookie['status']) {
			return [
				'status' => false,
				'response' => $check_cookie['response']
			];
		}		

		$csrftoken = InstagramCookie::GetCSRFCookie($cookie);

		return [
			'status' => true,
			'response' => [
				'userid' => $check_cookie['response']['userid'],
				'username' => $check_cookie['response']['username'], 
				'photo' => $check_cookie['response']['photo'],
				'cookie' => $cookie,
				'csrftoken' => $csrftoken
			]
		];

	}
}