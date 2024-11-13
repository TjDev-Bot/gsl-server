<?php

namespace App\Models;

use CodeIgniter\Model;

class UsersModel extends Model {

    protected $table = 'users';
    protected $primaryKey = 'id';
    protected $returnType = 'object';
    protected $useSoftDeletes = false;
    protected $useTimestamps = true;
    protected $validationRules = [];
    protected $validationMessages = [];
    protected $skipValidation = false;
    protected $allowedFields = [
        'uuid',
        'name',
        'email',
        'department',
        'location', 
        'role', 
        ];
    
    protected $beforeInsert = ['beforeInsert'];
    protected $beforeUpdate = ['beforeUpdate'];

    protected function beforeInsert(array $data) {
        /*
        $data['data']['pin'] = $this->passwordHash($data['data']['pin']); */
        $data['data']['created_at'] = date('Y-m-d H:i:s');
        return $data;
    }

    protected function beforeUpdate(array $data) {
        /*
        if (!empty($data['data']['pin'])) {
            $data['data']['pin'] = $this->passwordHash($data['data']['pin']);
        } */
        $data['data']['updated_at'] = date('Y-m-d H:i:s');
        return $data;
    }
    
    
    public function getUserByEmail($email) {
     return $this->where(['email' => $email])->first();
    }
    
    public function getUserByUuid($staff_uuid) {
     return $this->where(['staff_uuid' => $staff_uuid])->first();
    }
    
    public function getUsers() {
        return $this->findAll();
    }
    public function getCorpUsers() {
        return $this->where(['is_corp' => 'yes'])->findAll();
    }
}
