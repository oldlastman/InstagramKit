<?php

require "../../vendor/autoload.php";

use Riedayme\InstagramKit\InstagramPostUploadAPI;

$datacookie = 'yourcookie';

$filename = 'wi.mp4'; /* photo extensiun must .mp4 */
$caption = 'wow this is my videos'; /* caption text */

$postupload = new InstagramPostUploadAPI();
$postupload->SetCookie($datacookie);

$upload = $postupload->ProcessUploadVideo($filename);

if (!$upload['status']) {
	die($upload['response']);
}

$video_result['upload_id'] = $upload['response']['upload_id'];
$video_result['response'] = $upload['response']['data'];

sleep(5);

$convertimage =  'nx.jpg';
$upload = $postupload->ProcessUploadPhoto($convertimage,$video_result['upload_id']);
$configure = $postupload->ConfigureVideo($video_result,$caption);

echo "<pre>";
var_dump($configure);
echo "</pre>";