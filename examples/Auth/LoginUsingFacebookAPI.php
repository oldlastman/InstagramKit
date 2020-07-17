<?php  
require "../../vendor/autoload.php";

use Riedayme\InstagramKit\InstagramHelper;
use Riedayme\InstagramKit\InstagramAuthFacebookAPI;

/* 
get token data : 
view-source:https://www.facebook.com/login.php?skip_api_login=1&api_key=124024574287414&kid_directed_site=0&app_id=124024574287414&signed_next=1&next=https%3A%2F%2Fwww.facebook.com%2Fdialog%2Foauth%3Fclient_id%3D124024574287414%26redirect_uri%3Dhttps%253A%252F%252Fwww.instagram.com%252Faccounts%252Fsignup%252F%26scope%3Demail%26response_type%3Dtoken%252Cgranted_scopes%26ret%3Dlogin%26fbapp_pres%3D0%26logger_id%3D7e827784-0a07-46b4-8717-69a79802d91a&cancel_url=https%3A%2F%2Fwww.instagram.com%2Faccounts%2Fsignup%2F%3Ferror%3Daccess_denied%26error_code%3D200%26error_description%3DPermissions%2Berror%26error_reason%3Duser_denied%23_%3D_&display=page&locale=en_GB 
*/

$data = 'view-source:https://www.instagram.com/accounts/signup/?#access_token=xxxxxxxx&data_access_expiration_time=0&expires_in=0&granted_scopes=email%2Cads_management%2Cpublic_profile&denied_scopes=';

$token = InstagramHelper::ParseAccessToken($data);

$auth = new InstagramAuthFacebookAPI();

$results = $auth->Login($token);

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
    string(263) "https://instagram.fbog2-3.fna.fbcdn.net/v/t51.2885-19/44884218_345707102882519_2446069589734326272_n.jpg?_nc_ht=instagram.fbog2-3.fna.fbcdn.net&_nc_ohc=fTIyv_ZY0jwAX9ilf56&oh=db9d823c55164055e9a32d6bb1aa43dd&oe=5F3B398F&ig_cache_key=YW5vbnltb3VzX3Byb2ZpbGVfcGlj.2"
    ["cookie"]=>
    string(188) "target="";target="";target="";target="";target="";target="";target="";csrftoken=Po7NMXU2BzywXYq5NUZ13cuVVrSlbQ6Y;rur=PRN;ds_user_id=31310607724;sessionid=31310607724%xxx%3A16;"
    ["csrftoken"]=>
    string(32) "Po7NMXU2BzywXYq5NUZ13cuVVrSlbQ6Y"
  }
}
*/