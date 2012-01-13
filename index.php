<?php
error_reporting('E_ALL');
set_time_limit(0);

require_once 'class/facebookx.php';

$code = $_GET["code"];
$f = new facebookx();
$url = $f->loadToken($code,'http://facebook.localdomain/');
var_dump($url);
//$x = $f->getUser();
//var_dump($x);
$fql = 'SELECT uid,first_name,sex FROM user WHERE uid IN (SELECT uid2 FROM friend WHERE uid1=me()) ';
$data = $f->getFQL($fql);
echo count($data->data);
$male = 0;
$femail = 0;
$xxx = array();
foreach($data->data as $d){
    if($d->sex == 'male') $male++;
    else if($d->sex == 'female') $female++;
    else        array_push ($xxx, $d);
}
var_dump($male);
var_dump($female);
var_dump($xxx);
var_dump($data);
