<?php
namespace Users\Controllers;

use App\Controllers\BaseController;
use Users\Libraries\Microsoft;


class Microsoft extends BaseController {
    protected $microsoft;

    public function __construct() {
        $this->microsoft = new Microsoft();
    }

    public function mslogin() {
         if ($this->request->getMethod() == 'get') {
        $scope = 'openid email';
        $response = $this->microsoft->authurl($scope);
        return redirect()->to($response);
    } else {
       return redirect()->to(base_url() . '/login');
    }}
    
    public function mscallback() {
    if ($this->request->getMethod() == 'get') {

        $response = $this->microsoft->token();
        $jwt = explode('.', $response['id_token']);
      $userinfo = json_decode(base64_decode(str_replace('_', '/', str_replace('-','+',explode('.', $jwt)[1]))),true);
        if ($response->status != \Utils\Libraries\UtilsResponseLib::$SUCCESS) {
                $data['validation'] = $response->error->validation;
            } else {
                 session_regenerate_id(true);
                 unset(
    $_SESSION['state'],
    $_SESSION['code_verifier'],
    $_SESSION['code_challenge'],
    $_SESSION['code']
    
);
                $redirectUri = session()->getFlashdata('redirectUri');
                if ($redirectUri != '') {
                    return redirect()->to($redirectUri);
                } else {
                    return redirect()->to(base_url() . '/');
                }
            }
    } }

}
