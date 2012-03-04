<?php
/*
 * Class : facebookx 
 * Author : Kittinan Srithaworn
 * Date : 04/03/2012
 * Description : This is Simple Class For Facebook API
 * 
 * 
 * 
 */

class facebookx{
    
    private $appId = null;
    private $appSecret = null;
    private $callBackURL = null;
    private $permission = null;
    private $code = null;
    private $token = null;
    
    function __construct($appid,$appSecret,$callBack,$perm) {
        
        $this->appId = $appid;
        $this->appSecret =$appSecret;
        $this->callBackURL = $callBack;
        $this->permission = $perm;

        /*
         * For Canvas App  
         */
        if(!empty($_REQUEST['signed_request'])){
            $data = $this->parse_signed_request($_REQUEST['signed_request'], $this->appSecret);
            if(!empty($data['oauth_token'])){
                $this->token = 'access_token='.$data['oauth_token'];
            }
        }
    }
    
    public function getLoginUrl(){
        $url = 'https://www.facebook.com/dialog/oauth?client_id='.$this->appId;
        $url .= '&scope='.$this->permission;
        $url .= '&redirect_uri='.urlencode($this->callBackURL);
        return $url;
    }
    
    public function loadToken($code){
        $this->code = $code;
        $url = 'https://graph.facebook.com/oauth/access_token?client_id='.$this->appId;
        $url .= '&client_secret='.$this->appSecret;
        $url .= '&code='.$code;
        $url .= '&redirect_uri='.$this->callBackURL;
        $data = $this->httpGet($url);
        $status = preg_match('/^access.*/', $data, $matches);
        if($status)$this->token = $matches[0];
        else return false;
        return $this->token;
    }
    
    public function setToken($token){
        $this->token = $token;
    }
    public function getToken(){
        return $this->token;
    }

    
    public function graph($url,$params = '',$isPost = false){
        if(empty($url)) return false;
        if($isPost){
            $data = $this->httpPost('https://graph.facebook.com'.$url, $params);
        }else{
            if(is_array($params))$params = http_build_query($params);
            $data = $this->httpGet('https://graph.facebook.com'.$url.'?'.$this->token.'&'.$params);
        }
        if(!empty($data)) return json_decode ($data);
    }


    public function fql($fql){
        $fql = urlencode($fql);   
        $data = $this->httpGet('https://graph.facebook.com/fql?q='.$fql.'&'.$this->token);
        $data = json_decode($data);
        return $data;
    }
    
        
    public function getUser($friend_id = 'me'){
        return $this->graph('/'.$friend_id.'/');
    }
    
    /*
     * Facebook Post Wall Arguments
     * message, picture, link, name, caption, description, source
     * 
     */
    public function postWall($message,$picture,$link,$name,$caption,$description,$source,$friend = 'me'){
        $postData["message"] =$message;
        $postData["picture"] = $picture;
        $postData["link"] = $link;
        $postData["name"] = $name;
        $postData["caption"] = $caption;
        $postData["description"] = $description;
        $postData["source"] = $source;
        return $this->httpPost('https://graph.facebook.com/'.$friend.'/feed', $postData);
    }
    
    /*
     * Facebook Upload Photo
     * imagePath,message,tags
     * For tags pattern array(array('tag_uid'=> 'facebook_id','x' => 0,'y' => 0,))
     */
    public function uploadPhoto($imagePath,$message = null,$tags = null,$friend = 'me'){
        if(!file_exists($imagePath)) return false; 
        $params = array();
        $params['message'] = $message;
        $params['image'] = '@'.realpath($imagePath);
        $params['tags'] = $tags;
        return $this->httpPost('https://graph.facebook.com/'.$friend.'/photos', $params,true);
    }
    
    private function httpGet($url){
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
        $content = curl_exec ($ch);
        curl_close ($ch);
        return $content;
    }
    
    private function httpPost($url, $params = null, $is_upload = false) {
        if(!empty($params))$query = http_build_query($params);
        else $query = '';
        $ch = curl_init();
        $opts[CURLOPT_URL] =  $url.'?'.$this->token;
        $opts[CURLOPT_RETURNTRANSFER] = 1;
        if($is_upload) $opts[CURLOPT_POSTFIELDS] =$params;
        else $opts[CURLOPT_POSTFIELDS] = $query;

        $opts[CURLOPT_CONNECTTIMEOUT] = 30;
        curl_setopt_array($ch, $opts);
        $result = curl_exec ($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close ($ch);
        if ($status == 200) {
          return $result;
        }
        else {
          return false;
        }
  }
    
    private function parse_signed_request($signed_request, $secret) {
      list($encoded_sig, $payload) = explode('.', $signed_request, 2); 

      // decode the data
      $sig = $this->base64_url_decode($encoded_sig);
      $data = json_decode($this->base64_url_decode($payload), true);

      if (strtoupper($data['algorithm']) !== 'HMAC-SHA256') {
        error_log('Unknown algorithm. Expected HMAC-SHA256');
        return null;
      }

      // check sig
      $expected_sig = hash_hmac('sha256', $payload, $secret, $raw = true);
      if ($sig !== $expected_sig) {
        error_log('Bad Signed JSON signature!');
        return null;
      }

      return $data;
    }

    private function base64_url_decode($input) {
      return base64_decode(strtr($input, '-_', '+/'));
    }

}