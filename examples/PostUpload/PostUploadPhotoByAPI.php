<?php

require "../../vendor/autoload.php";

use Riedayme\InstagramKit\InstagramPostUploadAPI;
use Riedayme\InstagramKit\InstagramHelper;

$datacookie = 'yourcookie';

$filename = 'nx.jpg'; /* photo extensiun must .jpg */
$caption = 'Wow This is caption you...'; /* caption text */

$postupload = new InstagramPostUploadAPI();
$postupload->SetCookie($datacookie);

$upload = $postupload->ProcessUploadPhoto($filename);

if (!$upload['status']) {
	die($upload['response']);
}

$upload_id = $upload['response']['upload_id'];

$configure = $postupload->ConfigurePhoto($upload_id,$caption);

echo "<pre>";
var_dump($configure);
echo "</pre>";