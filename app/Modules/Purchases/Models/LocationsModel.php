<?php

namespace Locations\Models;

use CodeIgniter\Model;

class Locations extends Model {

    protected $table = 'locations';
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
        'job_title',
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
/*
    public function getPointLisasUsers() {
        return $this->where(['location' => 'point_lisas'])->findAll();
    }
    public function getChgUsers() {
        return $this->where(['location' => 'chaguanas'])->findAll();
    }
  
    
/*
    protected function passwordHash($pin) {
        return password_hash($pin, PASSWORD_DEFAULT);
    }
*/
}
