<?php
namespace Users\Controllers;

use App\Controllers\BaseController;
use Users\Libraries\UsersLib;
use Users\Validation\UserRules;
use CodeIgniter\I18n\Time;


class Profile extends BaseController {
    protected $usersLib;

    public function __construct() {
        $this->usersLib = new UsersLib();
    }

    public function profile() {

        $data = [
          'title'  => 'Profile'
            ];
        helper(['form']);

        if ($this->request->getMethod() == 'post') {
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
