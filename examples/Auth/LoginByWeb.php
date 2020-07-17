<?php  
require "../../vendor/autoload.php";

use Riedayme\InstagramKit\InstagramAuth;

$username = 'username';
$password = 'password';

$auth = new InstagramAuth();

$results = $auth->Login($username,$password);

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
    string(11) "31310607724"
    ["username"]=>
    string(8) "riedayme"
    ["photo"]=>
    string(274) "https://instagram.flos2-2.fna.fbcdn.net/v/t51.2885-19/44884218_345707102882519_2446069589734326272_n.jpg?_nc_ad=z-m&_nc_ht=instagram.flos2-2.fna.fbcdn.net&_nc_ohc=fTIyv_ZY0jwAX82EkRH&oh=6d6062e4b852187828917c1efad5725e&oe=5F3B398F&ig_cache_key=YW5vbnltb3VzX3Byb2ZpbGVfcGlj.2"
    ["cookie"]=>
    string(187) "target="";target="";target="";target="";target="";target="";target="";csrftoken=KVhmSw7cRz4kC1pkmIWMjdrZ0fmjT7uF;rur=PRN;ds_user_id=31310607724;sessionid=31310607724%xxx%3A1;"
    ["csrftoken"]=>
    string(32) "KVhmSw7cRz4kC1pkmIWMjdrZ0fmjT7uF"
  }
}
*/