<?php
namespace App\Controllers\Users;

use App\Controllers\BaseController;
use App\Libraries\MicrosoftAuth;
use App\Models\UsersModel;


class Login extends BaseController {
    protected $microsoft;

    public function __construct() {
        $this->microsoft = new MicrosoftAuth('https://gsl.jastech.co/mslogin', 'openid profile');
    }
    

    public function login() {
         $data = [
          'app_title' => 'GSL Portal',
             ];
         return view('Users/login', $data);
    }

  
    public function msauth() {
        
     //    if ($this->request->getMethod() == 'get') {
         $session = session();    
        $response = $this->microsoft->auth_url();
        return redirect()->to($response);
        session_write_close();
    //} else {
   //    return redirect()->to(base_url() . 'login');
  //  }
    
}
    
    public function mslogin() {
  //  if ($this->request->getMethod() == 'get') {
        $session = session();
        $response = $this->microsoft->token();
        
        
      $jwt = explode('.', $response['id_token']);
      
     

  // Extract the middle part, base64 decode it, then json_decode it
  $userinfo = json_decode(base64_decode($jwt[1]), true);
      $usersModel = new UsersModel;
        $user = $usersModel->getUserByEmail($userinfo['preferred_username']);
        if ($user->uuid == '') {
            $newData = [
           'id' => $user->id,
           'uuid'=> $userinfo['oid'],
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
        

return redirect()->to(base_url());

 
}

private function setUserLogged($user) {
        $data = [
            'id' => $user->id,
            'uuid' => $user->uuid,
            'email' => $user->email,
            'name' => $user->name,
            'isLoggedIn' => true
        ];
        session()->set($data);
        return true;
    }


public function logout() {
        if (session()->get('isLoggedIn')) {
            session()->destroy();
            return redirect()->to(base_url() . 'login');
        } else {
            return false;
        }
       
    }

    // query the user's table to get the user's name
    public function userName() {
        $usersModel = new UsersModel;
        $user = $usersModel->getUserByEmail(session()->get('name'));
        return $user->name;

    }



}