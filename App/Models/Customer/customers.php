<?php

declare(strict_types=1);
namespace App\Models\Customer;
use App\Models\Customer\Customer;

class Customers {
    private array $customerList;


    public function setCustomersfromFile(array $list){
        foreach($list as $row){
            if($row[0]==null)break;
            $newCustomer = new Customer();
            $newCustomer->set((int)$row[0], (string)$row[1], (string)$row[2], (string)$row[3],(float)$row[4]);
            $this->insertCustomerToList($newCustomer);
        }
    }

    public function insertCustomerToList(Customer $newCustomer){
        $this->customerList[]= $newCustomer;
    }

    public function getList(){
        return $this->customerList;
    }


}