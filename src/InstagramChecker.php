<?php namespace Riedayme\InstagramKit;

Class InstagramChecker
{	

	public static function CheckLiveCookie($cookie){

		$userid = InstagramCookie::GetUIDCookie($cookie);

		if (empty($userid)) {

			return [
				'status' => false,
				'response' => 'No userid found'
			];			

		} 
		
		$userinfo = InstagramResourceUser::GetUserInfoByID($userid);

		if (!$userinfo['status']) {
			return [
				'status' => false,
				'response' => $userinfo['response']
			];
		}

		$url = 'https://www.instagram.com/'.$userinfo['response']['username'].'/?__a=1';

		$headers = array();
		$headers[] = "Cookie: ".$cookie;

		$access = InstagramHelper::curl($url, false , $headers, false, InstagramUserAgent::Get('Windows'));

		$result = json_decode($access['body']);

		if(is_object($result) AND $result->graphql->user->restricted_by_viewer === false){

			/* Result Success on explore/instagram-username-a=1.json */

			return [
				'status' => true,
				'response' => [
					'userid' => $userid,
					'username' => $userinfo['response']['username'],
					'photo' => $userinfo['response']['photo']
				]
			];

		}else{

			return [
				'status' => false,
				'response' => 'Cookie Die'
			];		

		}

	}	
}