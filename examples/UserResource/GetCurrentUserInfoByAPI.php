<?php  
require "../../vendor/autoload.php";

use Riedayme\InstagramKit\InstagramResourceUser;

$cookie = 'yourcookie';

$read = new InstagramResourceUser();

$results = $read->GetCurrentUserInfoByAPI($cookie);

echo "<pre>";
var_dump($results);
echo "</pre>";

/*
array(2) {
  ["status"]=>
  bool(true)
  ["response"]=>
  array(2) {
    ["username"]=>
    string(14) "relaxing.media"
    ["photo"]=>
    string(243) "https://instagram.fcgk18-2.fna.fbcdn.net/v/t51.2885-19/s150x150/104307119_305434823951354_7692373176389732433_n.jpg?_nc_ht=instagram.fcgk18-2.fna.fbcdn.net&_nc_cat=102&_nc_ohc=as14Fa8pXF0AX9IrCPM&oh=939ce24f1c0dcb23c71a537632de272e&oe=5F3AAF27"
  }
}
*/