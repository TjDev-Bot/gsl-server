<?php
namespace Users\Controllers;

use App\Controllers\BaseController;
use Users\Libraries\UsersLib;
use Users\Validation\UserRules;


class Users extends BaseController {
    protected $usersLib;

    public function __construct() {
        $this->usersLib = new UsersLib();
    }


    public function createUser() {

        $data = [];
        helper(['form']);

        if ($this->request->getMethod() == 'post') {
            $response = $this->usersLib->createUser();
            if ($response->status != \Utils\Libraries\UtilsResponseLib::$SUCCESS) {
                $data['validation'] = $response->error->validation;
            } else {
                return redirect()->to(base_url() . '/login');
            }
        }

        return view('Users\Views\register', $data);
    }

    public function profile() {

        $data = [
          'title'  => 'Profile'
            ];
        helper(['form']);

        if ($this->request->getMethod() == 'post') {
            if ($this->request->getVar('fmode') == 'cancel') {
                session_regenerate_id(true);
                return redirect()->to(base_url() . '/');
            }
            $response = $this->usersLib->profile();
            if ($response->status != \Utils\Libraries\UtilsResponseLib::$SUCCESS) {
                $data['validation'] = $response->error->validation;
            } else {
                 session_regenerate_id(true);
                return redirect()->to(base_url() . '/profile');
            }
        }

        $data['user'] = $this->usersLib->getuserById();

        return view('Users\Views\profile', $data);
    }

}
