<?php

declare(strict_types=1);
namespace App\Models\Customer;
use App\Models\Customer\Customer;

class Customers {
    private array $customerList;

    public function __construct(array $list)
    {
        foreach($list as $row){
            if($row[0]==null)break;
            $newCustomer = new Customer();
            $newCustomer->setCustomer((string)$row[0], (string)$row[1], (string)$row[2],(float)$row[3]);
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