<?php

declare(strict_types=1);
namespace App\Controller\Customer;
use App\Models\Customer\Customer;
use App\Trait\FilehandlerTrait;

class CustomerController {
    use FilehandlerTrait;
    private Customer $authcustomer;

    public function __construct(Customer $customer){
        $this->authcustomer = $customer;
    }

    public function getAuthCustomerBalance(){
        $this->setAuthCustomerUpdatedInfo();
        return $this->authcustomer->getBalance();
    }

    private function setAuthCustomerUpdatedInfo(){
        $customers = $this->getItemsFromFile($this->authcustomer->getFileName());
        foreach($customers as $row){
            if($row[1] === $this->authcustomer->getEmail()){
                $this->authcustomer->setBalance((float)$row[3]);
                break;
            }
        }
    }


}