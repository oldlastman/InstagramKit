<?php  
require "../../vendor/autoload.php";

use Riedayme\InstagramKit\InstagramResourceUser;

$userid = '13320596140'; // faanteyki

$read = new InstagramResourceUser();

$results = $read->GetUserInfoByAPI($userid);

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
    string(9) "faanteyki"
    ["photo"]=>
    string(243) "https://instagram.fcgk18-2.fna.fbcdn.net/v/t51.2885-19/s150x150/101502769_618477598877338_2170620851571916800_n.jpg?_nc_ht=instagram.fcgk18-2.fna.fbcdn.net&_nc_cat=110&_nc_ohc=ltMc0jIt0rIAX_wMmXK&oh=22615ad65778a4147fd3828a946d54cf&oe=5F3AF28C"
  }
}
*/