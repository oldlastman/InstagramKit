<?php

require "../../vendor/autoload.php";

use Riedayme\InstagramKit\InstagramPostUploadAPI;

$datacookie = 'csrftoken=0b0Fsz7ALRzgMMEuwPpmIG0lklMecxUn;rur=FTW;ds_user_id=31310607724;urlgen=\"{\\\"36.77.220.57\\\": 7713}:1jwOVG:-XKrcxc8rT5ROzlKYSBPhG3TnJg\";sessionid=31310607724%3AJFXMkUiYIk9jTW%3A10;';

$caption = 'Wow This is caption you... album upload'; /* caption text */

$filedata = [ 
	[
		'type'     => 'image',
		'file'     => 'nx.jpg', 
	],
	[
		'type'     => 'image',
		'file'     => 'nxr.jpg', 
	],
    // [
    //     'type'     => 'video',
    //     'file'     => '', 
    // ],    
];

$postupload = new InstagramPostUploadAPI();
$postupload->SetCookie($datacookie);

$medias = [];
foreach ($filedata as $media) {

	if ($media['type'] == 'image') {

		$upload = $postupload->ProcessUploadPhoto($media['file'],false,true);

		if (!$upload['status']) {
			die($upload['response']);
		}

		$upload_id = $upload['response']['upload_id'];


	}elseif ($media['type'] == 'video') {

		$upload = $postupload->ProcessUploadVideo($media['file'],true);

		if (!$upload['status']) {
			die($upload['response']);
		}

		$upload_id = $upload['response']['upload_id'];

		sleep(5);

		$convertimage =  'nx.jpg';
		$upload = $postupload->ProcessUploadPhoto($convertimage,$upload_id);

	}

	$medias[] = array_merge($media,array('upload_id' => $upload_id));
}

$configure = $postupload->ConfigureAlbum($medias,$caption);

echo "<pre>";
var_dump($configure);
echo "</pre>";