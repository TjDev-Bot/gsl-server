<?php
namespace Customer\Models;

use CodeIgniter\Model;
use CodeIgniter\Database\ConnectionInterface;

class CustomerModel extends Model {

    protected $db;
    public function __construct(ConnectionInterface &$db) {
        $this->db =& $db;

        $this->table = 'customer_info';
    }

    public function addEntry($data) {
        $this->db
                ->table($this->table)
                    ->insert($data);
        return $this->db->insertID();
    }

    public function updateEntry($where, $data) {
        return $this->db
                ->table($this->table)
                    ->where($where)
                    ->set($data)
                    ->update();
    }

    public function deleteEntry($where) {
        return $this->db
                ->table($this->table)
                    ->where($where)
                    ->delete();
    }

    public function getEntry($where) {
        return $this->db
                ->table($this->table)
                    ->where($where)
                    ->get()
                    ->getRow();

    }

    public function getEntryList($where = 0) {
        if($where) {
            return $this->db
                    ->table($this->table)
                ->where($where)
                ->get()
                ->getResult();
        } else {
            return $this->db
                    ->table($this->table)
                ->get()
                ->getResult();
        }
    }

    public function getNumRows($where) {
        return $this->db
                ->table($this->table)
                    ->where($where)
                    ->get()
                    ->getNumRows();

    }
}
