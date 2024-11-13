<?php
namespace Customer\Controllers;

use App\Controllers\BaseController;
use Customer\Models\CustomerModel;

use Customer\Models\CustomerDeferModel;

class Customer extends BaseController {

    public function __construct() {
        $db                     = db_connect();
        $this->session          = \Config\Services::session();

        $this->customer         = new CustomerModel($db);
        $this->customerDefer    = new CustomerDeferModel($db);
        $this->datetime         = date("Y-m-d H:i:s");

    }

    public function index() {
        $this->list();
    }

    public function list() {
        $data                   = [];
        $data ['content_title'] = 'Customers';
        echo view('Customer\Views\list', $data);
    }

    public function edit() {
        $id         = $this->request->getPost('id');
        $result     = $this->customer->getEntry(['id' => $id]);
        echo view('Customer\Views\components\edit', $result);
    }
    
    public function create() {
        $name               = $this->request->getPost('name');
        $email              = $this->request->getPost('email');
        $address            = $this->request->getPost('address');
        $mobile_number      = $this->request->getPost('mobile_number');

        $where      = [
        'email'  => $email,
    ];
        $has_account = $this->customer->getEntry($where);
        if($has_account) {
            $json = [
            'status'    => false,
                    'message'   => showDangerMessage("Entered email address is already exists."),
        ];
        } else {
            $data = [
            'name'              => $name,
            'email'             => $email,
            'address'           => $address,
            'mobile_number'     => $mobile_number,
            'created_at'        => $this->datetime,
            'status'            => "1",
        ];
            $result = $this->customer->addEntry($data);
            if($result) {
                $json = [
                'status'    => true,
                        'message'   => showSuccessMessage("New record has been created successfully"),
            ];
            } else {
                $json = [
                'status'    => false,
                        'message'   => showDangerMessage("Something went wrong. Please try again!"),
            ];
            }
        }
        echo json_encode($json);
    }

    public function update() {
        $id               = $this->request->getPost('id');
        $name               = $this->request->getPost('name');
        $email              = $this->request->getPost('email');
        $address            = $this->request->getPost('address');
        $mobile_number      = $this->request->getPost('mobile_number');

        $where      = [
        'id'  => $id,
    ];
            $data = [
            'name'              => $name,
            'email'             => $email,
            'address'           => $address,
            'mobile_number'     => $mobile_number,
        ];
            $result = $this->customer->updateEntry(['id' => $id], $data);
            if($result) {
                $json = [
                'status'    => true,
                        'message'   => showSuccessMessage("Selected record has been updated successfully"),
            ];
            } else {
                $json = [
                'status'    => false,
                        'message'   => showDangerMessage("Something went wrong. Please try again!"),
            ];
        }
        echo json_encode($json);
    }

    public function delete() {
        $id         = $this->request->getPost('id');
        $result     = $this->customer->deleteEntry(["id" => $id]);
        if($result) {
            $json = [
            'message'   => showSuccessMessage("The selected record has been deleted successfully."),
                    'status'    => true,
            ];
        } else {
            $json = [
            'message'   => showDangerMessage("Something went wrong. Please try again."),
                    'status'    => false,
        ];
        }
        echo json_encode($json);
    }

    public function datatable() {
        $postData       = $this->request->getPost();
        $i              = $this->request->getPost('start');
        $result         = $this->customerDefer->getRows($postData);
        $arrayList      = $this->getRows($i, $result);
        $output = array(
                "draw"              => $this->request->getPost('draw'),
                "recordsTotal"      => $this->customerDefer->countAll($this->request->getPost()),
                "recordsFiltered"   => $this->customerDefer->countFiltered($this->request->getPost()),
                "data"              => $arrayList,
    );
        echo json_encode($output);
    }

    function getRows($i, $result) {
        $arrayList = [];
        foreach($result as $row) {
            $action = ' <button type="button" name="btn-edit" class="btn btn-sm btn-primary"  data-toggle="modal" data-target="#modal-update" data-id="'.$row->id.'" title="Edit">Edit</button>';
            $action .= ' <button type="button" name="btn-delete" class="btn btn-sm btn-danger" data-id="'.$row->id.'" title="Delete">Delete</button>';
            $arrayList [] = [
            ++$i,
                    $row->name,
                    $row->email,
                    $row->mobile_number,
                    $row->address,
                    $action,
        ];
        }
        return $arrayList;
    }
}
