<?php namespace Riedayme\InstagramKit;

Class InstagramPostUploadAPI
{

	public $cookie;	
	public $csrftoken;

	public $upload_id;
	public $results_UploadPhoto;
	public $result_CaptionPhoto;

	public function SetCookie($data) 
	{
		$this->cookie = $data;
		$this->csrftoken = InstagramCookie::GetCSRFCookie($data);
	}
	
	public function ProcessUploadPhoto($binary_file,$upload_id = false,$upload_album = false)
	{

		if (!$upload_id) {
			$upload_id = number_format(round(microtime(true) * 1000), 0, '', '');
		}

		$url = sprintf('https://i.instagram.com/rupload_igphoto/%s_%d_%d',
			$upload_id,
			0,
			InstagramHelperAPI::hashCode($binary_file)
		);

		$filedata = file_get_contents($binary_file);

		$upload_params = [
			'upload_id'         => (string) $upload_id,
			'retry_context'     => json_encode([
				'num_step_auto_retry'   => 0,
				'num_reupload'          => 0,
				'num_step_manual_retry' => 0,
			]),
			'image_compression' => '{"lib_name":"moz","lib_version":"3.1.m","quality":"87"}',
			'xsharing_user_ids' => json_encode([]),
			'media_type'        => '1',
		];		

		if ($upload_album) {
			$upload_params = array_merge($upload_params,array('is_sidecar' => '1'));
		}

		// echo json_encode($upload_params);
		// exit;

		$headers = array();
		$headers[] = 'X_FB_PHOTO_WATERFALL_ID: '.InstagramHelperAPI::generateUUID(true);		
		$headers[] = 'X-Instagram-Rupload-Params: '.json_encode(InstagramHelperAPI::reorderByHashCode($upload_params));
		$headers[] = 'X-Entity-Type: image/jpeg';
		$headers[] = 'X-Entity-Name: '.$binary_file;
		$headers[] = 'X-Entity-Length: '.strlen($filedata);
		$headers[] = 'Offset: 0';

		$access = InstagramHelperAPI::curl($url, $filedata , $headers , $this->cookie , InstagramUserAgent::Get('Android'));

		echo $access['body'].PHP_EOL;

		$response = json_decode($access['body'],true);
		
		if ($response['status'] == 'ok') {
			return [
				'status' => true,
				'response' => [
					'upload_id' => $upload_id
				]
			];
		}else{
			return [
				'status' => false,
				'response' => $access['body']
			];
		}
	}

	public function ConfigurePhoto($upload_id,$caption)
	{

		$url = "https://i.instagram.com/api/v1/media/configure/?timezone_offset=".date('Z');

		$date = date('Y:m:d H:i:s');

		$postdata = [
			'upload_id' => $upload_id,
			'date_time_original' => $date,
			'date_time_digitalized' => $date,
			'caption' => $caption,
			'source_type' => '4',
			'media_folder' => 'Camera'
		];

		$access = InstagramHelperAPI::curl($url, $postdata , false , $this->cookie , InstagramUserAgent::Get('Android'));

		//echo $access['body'].PHP_EOL;

		$response = json_decode($access['body'],true);
		
		if ($response['status'] == 'ok') {
			return [
				'status' => true,
				'response' => [
					'id' => $response['media']['pk'],
					'code' => $response['media']['code'],
					'url' => "https://www.instagram.com/p/{$response['media']['code']}/"
				]
			];
		}else{
			return [
				'status' => false,
				'response' => $access['body']
			];
		}
	}	

	public function ProcessUploadVideo($file_name,$upload_album = false)
	{

		$upload_id = number_format(round(microtime(true) * 1000), 0, '', '');

		$url = sprintf('https://i.instagram.com/rupload_igvideo/%s',
			InstagramHelperAPI::generateUUID(true)
		);	

		$filedata = file_get_contents($file_name);		

		$upload_params = [
			'upload_id'                => (string) $upload_id,
			'retry_context'     => json_encode([
				'num_step_auto_retry'   => 0,
				'num_reupload'          => 0,
				'num_step_manual_retry' => 0,
			]),
			'xsharing_user_ids'        => json_encode([]),
			'media_type'               => '2',
			'potential_share_types'    => json_encode(['not supported type']),
		];		

		if ($upload_album) {
			$upload_params = array_merge($upload_params,array('is_sidecar' => '1'));
		}

		$upload_params = InstagramHelperAPI::reorderByHashCode($upload_params);

		$headers = array();	
		$headers[] = 'X-Instagram-Rupload-Params: '.json_encode($upload_params);
		$headers[] = 'X-Entity-Type: video/mp4';
		$headers[] = 'X-Entity-Name: '.$file_name;
		$headers[] = 'X-Entity-Length: '.strlen($filedata);
		$headers[] = 'Content-Length: '.strlen($filedata);		
		$headers[] = 'Offset: 0';				

		$access = InstagramHelperAPI::curl($url, $filedata , $headers , $this->cookie , InstagramUserAgent::Get('Android'));

		//echo $access['body'].PHP_EOL;

		$response = json_decode($access['body'],true);
		
		if ($response['status'] == 'ok') {

			return [
				'status' => true,
				'response' => [
					'upload_id' => $upload_id
				]
			];

		}else{
			return [
				'status' => false,
				'response' => $access['body']
			];
		}
	}	

	public function ConfigureVideo($upload_id,$caption)
	{

		$url = "https://i.instagram.com/api/v1/media/configure/?video=1";

		$data = [
			'upload_id' => (string) $upload_id,
			'video_result' => "",
			'caption' => $caption,
			'poster_frame_index' => "0",
			'length' => "0",
			'audio_muted' => "false",
			'filter_type' => "0",
			'source_type' => "4",
		];

		$postdata = InstagramHelperAPI::generateSignatureUpload(json_encode($data));

		$access = InstagramHelperAPI::curl($url, $postdata , false , $this->cookie , InstagramUserAgent::Get('Android'));

		//echo $access['body'];
		// exit;

		$response = json_decode($access['body'],true);
		
		if ($response['status'] == 'ok') {
			return [
				'status' => true,
				'response' => [
					'id' => $response['media']['pk'],
					'code' => $response['media']['code'],
					'url' => "https://www.instagram.com/p/{$response['media']['code']}/"
				]
			];
		}else{
			return [
				'status' => false,
				'response' => $access['body']
			];
		}
	}

	public function ConfigureAlbum($medias,$caption)
	{

		$url = "https://i.instagram.com/api/v1/media/configure_sidecar/";

		$upload_id = number_format(round(microtime(true) * 1000), 0, '', '');

		$date = date('Y:m:d H:i:s');

		$childrenMetadata = [];
		foreach ($medias as $media) {

			if ($media['type'] == 'image') {
				$childrenMetadata[] = [
					'upload_id' => $media['upload_id'],
					'source_type' => '4'
				];
			}elseif ($media['type'] == 'video') {
				$childrenMetadata[] = [
					'date_time_original'  => $date,
					'poster_frame_index'  => 0,
					'upload_id' => (string) $media['upload_id'],
					'source_type' => "4"
				];
			}

		}

		$data = [
			'timezone_offset' => date('Z'),
			'client_sidecar_id' => $upload_id,
			'caption' => $caption,
			'children_metadata' => $childrenMetadata
		];

		$postdata = InstagramHelperAPI::generateSignatureUpload(json_encode($data));

		$access = InstagramHelperAPI::curl($url, $postdata , false , $this->cookie , InstagramUserAgent::Get('Android'));

		echo json_encode($access['body']);
		exit;

		$response = json_decode($access['body'],true);
		
		if ($response['status'] == 'ok') {
			return [
				'status' => true,
				'response' => [
					'id' => $response['media']['pk'],
					'code' => $response['media']['code'],
					'url' => "https://www.instagram.com/p/{$response['media']['code']}/"
				]
			];
		}else{
			return [
				'status' => false,
				'response' => $access['body']
			];
		}
	}	
}