<?php

namespace App\Libraries;
use CodeIgniter\HTTP\Response;
use CodeIgniter\HTTP\ResponseRedirect;


class MicrosoftAuth
{
    const CLIENT_ID = '0f6bde14-27c9-4827-9bf2-b9ff5e8a2fe6';
    const CLIENT_SECRET = 'BN68Q~A2Yp3kwy.HdUz5Tw55PKcz7YPWolRnmbmB';
    const AUTHORIZE_URL = 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize';
    const TOKEN_URL = 'https://login.microsoftonline.com/common/oauth2/v2.0/token';
    private $redirect_uri;
    private $scope;
    
   public function __construct($redirect_uri, $scope)
	{
		$this->redirect_uri = $redirect_uri;
		$this->scope = $scope;
	//	$config = config(App::class);
     //   $this->response = new Response($config);
	}




    public function auth_url()
    {
    
       function base64url_encode($plainText)
{
    $base64 = base64_encode($plainText);
    $base64 = trim($base64, "=");
    $base64url = strtr($base64, '+/', '-_');
    return ($base64url);
}
  
  $_SESSION['state'] = bin2hex(openssl_random_pseudo_bytes(16));
  $_SESSION['code_verifier'] = base64url_encode(pack('H*', bin2hex(openssl_random_pseudo_bytes(32))));
  $_SESSION['code_challenge'] = base64url_encode(pack('H*', hash('sha256', $_SESSION['code_verifier'])));
  
 
  $params = array(
    'client_id' => self::CLIENT_ID,
    'response_type' => 'code',
    'redirect_uri' => $this->redirect_uri,
    'response_mode' => 'query',
    'scope' => $this->scope,
    'state' => $_SESSION['state'],
    'code_challenge' => $_SESSION['code_challenge'],
    'code_challenge_method' => 'S256'
  );
  
  $options = http_build_query($params);
   $auth_url = self::AUTHORIZE_URL . '?' . $options;
  
  // Redirect the user to Google's authorization page
  return $auth_url;

}


function token() {
    
    $receivedState = htmlspecialchars($_GET["state"]);
   $expectedState = $_SESSION['state'];
    
    $_SESSION['code'] = htmlspecialchars($_GET['code']);
    
   if($receivedState == $expectedState) {

  // Exchange the auth code for a token
  $ch = curl_init(self::TOKEN_URL);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    'grant_type' => 'authorization_code',
    'client_id' => self::CLIENT_ID,
    'scope' => $this->scope,
    'code' => $_SESSION['code'],
    'redirect_uri' => $this->redirect_uri,
    'code_verifier'=> $_SESSION['code_verifier'],
    'client_secret' => self::CLIENT_SECRET,
    
  ]));
  
 $response = curl_exec($ch);
 $token = json_decode($response, true);
  return $token;

}}
}