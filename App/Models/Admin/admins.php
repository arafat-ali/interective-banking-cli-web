<?php

declare(strict_types=1);
namespace App\Models\Admin;
use App\Models\Admin\Admin;

class Admins {
    private array $adminList;

    public function __construct(array $list)
    {
        foreach($list as $row){
            if($row[0]==null)break;
            $newAdmin = new Admin();
            $newAdmin->setAdmin((string)$row[0], (string)$row[1], (string)$row[2]);
            $this->insertAdminToList($newAdmin);
        }
    }

    public function insertAdminToList(Admin $newAdmin){
        $this->adminList[]= $newAdmin;
    }

    public function getList(){
        return $this->adminList;
    }


}