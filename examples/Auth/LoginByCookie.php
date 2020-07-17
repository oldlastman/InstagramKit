<?php  
require "../../vendor/autoload.php";

use Riedayme\InstagramKit\InstagramAuthCookie;

$cookie = 'yourcookie';

$auth = new InstagramAuthCookie();

$results = $auth->Login($cookie);

echo "<pre>";
var_dump($results);
echo "</pre>";

/*
array(2) {
  ["status"]=>
  bool(true)
  ["response"]=>
  array(5) {
    ["userid"]=>
    string(10) "9868652404"
    ["username"]=>
    string(14) "relaxing.media"
    ["photo"]=>
    string(243) "https://instagram.fcgk18-2.fna.fbcdn.net/v/t51.2885-19/s150x150/104307119_305434823951354_7692373176389732433_n.jpg?_nc_ht=instagram.fcgk18-2.fna.fbcdn.net&_nc_cat=102&_nc_ohc=as14Fa8pXF0AX9IrCPM&oh=939ce24f1c0dcb23c71a537632de272e&oe=5F3AAF27"
    ["cookie"]=>
    string(242) "ds_user=relaxing.media;csrftoken=XBTbPPZDc95cg1qYLWgHo7rnSsnVEpQJ;rur=PRN;mid=Xw_HoQABAAFzOdA8s8TocHJns7QX;ds_user_id=9868652404;urlgen="{\"180.252.89.35\": 7713}:1jvuSJ:A3XkaHPLyHsdV_70Sk2YGBi08ok";sessionid=9868652404%xxx%3A13;"
    ["csrftoken"]=>
    string(32) "XBTbPPZDc95cg1qYLWgHo7rnSsnVEpQJ"
  }
}
*/