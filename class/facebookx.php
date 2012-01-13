<?php

class facebookx{
    
    private  $appId;
    private  $appSecret;
    private  $callBackURL;
    private  $permission;
    
    private $code;
    private $token;
    
    function __construct($appid = '301010343278878',$appSecret = 'a13163c66a8350ab1af98aa6a6f04e9a',$callBack = 'http://facebook.localdomain/',$perm = 'email,read_stream') {
        $this->appId = $appid;
        $this->appSecret =$appSecret;
        $this->callBackURL = $callBack;
        $this->permission = $perm;
        
    }
    
    function getLoginUrl(){
        $url = 'https://www.facebook.com/dialog/oauth?client_id='.$this->appId;
        $url .= '&scope='.$this->permission;
        $url .= '&redirect_uri='.$this->callBackURL;
        return $url;
    }
    
    function loadToken($code){
        $this->code = $code;
        $url = 'https://graph.facebook.com/oauth/access_token?client_id='.$this->appId;
        $url .= '&client_secret='.$this->appSecret;
        $url .= '&code='.$code;
        $url .= '&redirect_uri='.$this->callBackURL;
        $data = $this->loadWeb($url);
        $status = preg_match('/^access.*/', $data, $matches);
        if($status)$this->token = $matches[0];
        else return false;
        return $this->token;
    }
    
    function setToken($token){
        $this->token = $token;
    }
    function getToken(){
        return $this->token;
    }
    
    function getGraphAPI($graphURL){
        $data = $this->loadWeb($graphURL.'?'.$this->token);
        $data = json_decode($data);
        return $data;
    }
    
    function getUser(){
        return $this->getGraphAPI('https://graph.facebook.com/me');
    }
    
    function getFQL($fql){
        $fql = urlencode($fql);   
        $data = $this->loadWeb('https://graph.facebook.com/fql?q='.$fql.'&'.$this->token);
        $data = json_decode($data);
        return $data;
    }
    
    function loadWeb($url){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
        $content = curl_exec ($ch);
        curl_close ($ch);
        return $content;
    }
    
    
    
    
    
}