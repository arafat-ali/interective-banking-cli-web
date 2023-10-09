<?php

declare(strict_types=1);
namespace App\Controller\Customer;

use App\Models\Customer\Customers;
use App\Models\Customer\Customer;
use App\Validator\Validator;
use App\Trait\FilehandlerTrait;

class AuthController {
    use FilehandlerTrait;
    private Customers $customers;
    private Customer $customer;
    private Validator $validator;

    public function __construct()
    {
        $this->validator = new Validator();
        $this->customer = new Customer();
        $this->customers = new Customers($this->getItemsFromFile($this->customer->getFileName()));
    }

    public function getCustomer():Customer{
        return $this->customer;
    }


    public function Login():bool{
        $email = $this->validator->getEmailWithValidation();
        $password = $this->validator->getPasswordWithValidation();

        $customer = $this->validator->isUserExist($email, $this->customers->getList());
        if($customer === null){
            echo "Account not found!\n\n";
            return false;
        }

        if($customer->getPassword() !== md5($password)){
            echo "\nInvalid credential!\n\n";
            return false;
        }
        $this->customer = $customer;
        return true;
    }


    public function register():bool{
        $name = (string) trim(readline('Please insert your name: '));
        $email = $this->validator->getEmailWithValidation();
        $password = $this->validator->getPasswordWithValidation();
        

        if($this->validator->isUserExist($email, $this->customers->getList()) !==null){
            echo "Account already available with this email!";
            return false;
        }

        $registerSuccess = false;
        //Insert data into file
        $insertIntoFileStatus = $this->insertNewItemIntoFile($this->customer->getFileName(), [ $name, $email, md5($password), 0]);

        if($insertIntoFileStatus){
            $this->customer->setCustomer($name, $email, md5($password), 0);
            $this->customers->insertCustomerToList($this->customer);
            $registerSuccess = true;
        }

        if(!$registerSuccess) {
            echo "\nSomething happened bad!\n\n";
            return false;
        }
        echo "\nSuccessfully Registerred\n\n";
        return $registerSuccess;
    }


}