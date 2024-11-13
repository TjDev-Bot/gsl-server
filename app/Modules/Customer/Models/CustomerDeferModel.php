<?php namespace Customer\Models;

use CodeIgniter\Model;
use CodeIgniter\Database\ConnectionInterface;

class CustomerDeferModel extends Model {

    protected $db;
    protected $table;
    protected $column_order;
    protected $column_search;
    protected $builder;
    public function __construct(ConnectionInterface &$db) {
        $this->db               =& $db;
        $this->table            = 'customer_info';
        $field_names            = $this->db->getFieldNames($this->table);
        $this->column_order     = $this->getColumnOrder($field_names);
        $this->column_search    = $this->getColumnSearch($field_names);
    }

    public function getColumnOrder($field_names) {
        $column_order           = [];
        $column_order[0]        = null;
        foreach($field_names as $row) {
            $column_order []    = $row;
        }
        return $column_order;
    }

    public function getColumnSearch($field_names) {
        $column_order           = [];
        foreach($field_names as $row) {
            $column_order []    = $row;
        }
        return $column_order;
    }
    
     public function getRows($postData) {
        $this->_get_datatables_query($postData);
        if($postData['length'] != -1){
            $this->builder->limit($postData['length'], $postData['start']);
        }
        $query = $this->builder->get();
        return $query->getResult();
    }

    public function countAll($postData) {
        $this->builder = $this->db->table($this->table);
        return $this->builder->countAllResults();
    }

    public function countFiltered($postData) {
        $this->_get_datatables_query($postData);
        $query = $this->builder->get();
        return $this->builder->countAll();
    }

    private function _get_datatables_query($postData) {

        $this->builder = $this->db->table($this->table)
                            ->orderBy('id', 'DESC');
        $i = 0;
        // loop searchable columns 
        foreach($this->column_search as $item){
            // if datatable send POST for search
            if($postData['search']['value']){
                // first loop
                if($i===0){
                    // open bracket
                    $this->builder->groupStart();
                    $this->builder->like($item, $postData['search']['value']);
                } else{
                    $this->builder->orLike($item, $postData['search']['value']);
                }
                    // last loop
                if(count($this->column_search) - 1 == $i){
                    // close bracket
                    $this->builder->groupEnd();
                }
            }
            $i++;
        }

        if(isset($postData['order'])){
            $this->builder->orderBy($this->column_order[$postData['order']['0']['column']], $postData['order']['0']['dir']);
        }else if(isset($this->order)){
            $order = $this->order;
            $this->builder->orderBy(key($order), $order[key($order)]);
        }
    }
}