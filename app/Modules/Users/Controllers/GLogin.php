<?php
namespace Users\Controllers;

use App\Controllers\BaseController;
//use Users\Libraries\GoogleAuth;
//use Users\Models\UsersModel;


class GLogin extends BaseController {
   // protected $google;

   // public function __construct() {
      //  $this->google = new GoogleAuth('https://apply.nesc.edu.tt/glogin', 'openid email profile');
   // }

    public function login() {
        /*
         $data = [
          'app_title' => 'Staff Portal',
          'login_logo'=> '',
          'main_image' => '',
          'background_color' => '',
          'highlight_color'=> ''
             ];
        */
        return 'Hello World';
    }

    
    public function gauth() {
         if ($this->request->getMethod() == 'get') {
        $response = $this->google->auth_url();
        return redirect()->to($response);
        session_write_close();
    } else {
       return redirect()->to(base_url() . '/login');
    }}
    
    public function glogin() {
    if ($this->request->getMethod() == 'get') {
        $response = $this->google->token();
        
        
      $jwt = explode('.', $response['id_token']);
      
     

  // Extract the middle part, base64 decode it, then json_decode it
  $userinfo = json_decode(base64_decode($jwt[1]), true);
  

        $usersModel = new UsersModel;
        $user = $usersModel->getUserByEmail($userinfo['email']);
        if ($user->uuid == 'unknown') {
            $newData = [
           'id' => $user->id,
           'uuid'=> $userinfo['sub'],
           'name'=> $userinfo['name']
            ];
           $usersModel->save($newData);
        }
                        unset(
    $_SESSION['state'],
    $_SESSION['code_verifier'],
    $_SESSION['code_challenge'],
    $_SESSION['code']
    
);
            session_regenerate_id(true);
             $this->setUserLogged($user);
        

return redirect()->to(base_url() . '/');


}}

private function setUserLogged($user) {
        $data = [
            'id' => $user->id,
            'uuid' => $user->uuid,
            'email' => $user->email,
            'role' => $user->role,
            'location' => $user->location,
            'isLoggedIn' => true
        ];
        session()->set($data);
        return true;
    }


    public function logout() {
        if (session()->get('isLoggedIn')) {
            session()->destroy();
             return redirect()->to(base_url() . '/');
        } else {
            return false;
        }
       
    }

}
