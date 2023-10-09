<?php

declare(strict_types=1);
namespace App\Controller\Admin;
use App\Models\Admin\Admin;
use App\Models\Customer\Customers;
use App\Models\Customer\Customer;
use App\Models\Customer\Transactions;
use App\Models\Customer\Transaction;
use App\Trait\FilehandlerTrait;
use App\Validator\Validator;

class UserReportController {
    use FilehandlerTrait;
    private Admin $authuser;
    private Customers $customers;
    private Transactions $transactions;

    public function __construct(Admin $admin){
        $this->authuser = $admin;
        // $this->customers = $this->getItemsFromFile((new Customer)->getFileName());
        // $this->transactions = $this->getItemsFromFile((new Transaction)->getFileName());

        // $this->transactions = new Transactions($this->getItemsFromFile((new Transaction)->getFileName()));
        
        // print_r($this->transactions);
        $this->transactions = new Transactions();
        $this->customers = new Customers($this->getItemsFromFile((new Customer())->getFileName()));
    }

    public function getAllCustomer(){
        foreach($this->customers->getList() as $customer){
            printf("Name: %s, Email: %s, Balance: %.2f\n", $customer->getName(), $customer->getEmail(), $customer->getBalance());
        }
    }

    public function getAllUserTransactions(){
        $this->transactions->setTransactionListOfAllUser($this->getItemsFromFile((new Transaction)->getFileName()),$this->customers->getList());
        $this->viewTransactionData();
    }

    public function getTransactionsOfSpecificUser(){
        $validator = new Validator();
        $email = $validator->getEmailWithValidation();

        $customer = $validator->isUserExist($email, $this->customers->getList());
        if($customer === null){
            printf("\nAccount with this email - %s not exists!\n", $email);
            return false;
        }

        $this->transactions->setTransactionListOfCustomer($this->getItemsFromFile((new Transaction)->getFileName()),$customer);

        printf("\nAll Transaction History of - %s -\n\n", $customer->getName());
        $this->viewTransactionData();
    }


    private function viewTransactionData(){
        $dataFound = false;
        foreach($this->transactions->getList() as $transaction){
            $dataFound = true;
            printf(
                "Name - %s \t Email - %s, %s - %.2f BDT at %s\n", 
                $transaction->customer->getName(), $transaction->customer->getEmail(), $transaction->type->value, $transaction->amount, $transaction->date);
        }
        if(!$dataFound){
            printf("\nNo Transaction History found\n\n");
        }
    }




}