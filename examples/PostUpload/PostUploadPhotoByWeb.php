<?php

require "../../vendor/autoload.php";

use Riedayme\InstagramKit\InstagramPostUploadPhoto;
use Riedayme\InstagramKit\InstagramHelper;

$datacookie = 'yourcookie';

//$filename = 'nx.png'; /* photo extensiun must .jpg */
$caption = 'Wow This is caption you...'; /* caption text */

// convert image to jpeg
$convertimage =  'nx.jpg';
// $convertimage =  InstagramHelper::convertToJpeg($filename,explode('.', $filename)[0].'.jpg');

$postupload = new InstagramPostUploadPhoto();
$postupload->SetCookie($datacookie);

$upload = $postupload->Process($convertimage);

if (!$upload['status']) {
	die($upload['response']);
}

$upload_id = $upload['response']['upload_id'];

$configure = $postupload->Configure($upload_id,$caption);

echo "<pre>";
var_dump($configure);
echo "</pre>";