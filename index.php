<?php
error_reporting('E_ALL');
set_time_limit(0);

require_once 'class/facebookx.php';

$appID = '103301849796687';
$appSecret = 'e0fd2391f353738385378b935e9945aa';
$perm = 'email,read_stream,publish_stream,user_photos';
$redirect = 'http://facebook.localdomain/';


$fb = new facebookx($appID,$appSecret,$redirect,$perm);


if(!empty($_GET["code"])){
    $fb->loadToken($_GET["code"]);
}

$token = $fb->getToken();
if(empty($token)){
    echo 'Not install App please go to <br>';
    echo $fb->getLoginUrl();
    exit(0);
}

echo "Token is $token";
echo '<br><br>';

//User
echo '<h1>getUser()</h1>';
$user = $fb->getUser();
echo '<pre>';
var_dump($user);
echo '</pre>';


echo '<h1>Sample Graph API</h1>';
echo '/me/feed';
$fql = $fb->graph('/me/feed');
echo '<pre>';
var_dump($fql);
echo '</pre>';

//fql
echo '<h1>Sample FQL</h1>';
$fql = $fb->fql('SELECT uid2 FROM friend WHERE uid1=me()');
echo '<pre>';
var_dump($fql);
echo '</pre>';

