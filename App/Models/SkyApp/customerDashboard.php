<?php
declare(strict_types=1);

namespace App\Models\SkyApp;
use App\Models\Customer\Customer;
use App\Controller\Customer\TransactionController;
use App\Controller\Customer\CustomerController;

class CustomerDashboard{
    private Customer $authCustomer;
    private TransactionController $transactionController;
    private CustomerController $customerController;
    

    public function __construct(Customer $customer){
        $this->authCustomer = $customer;
        $this->transactionController = new TransactionController($this->authCustomer);
        $this->customerController = new CustomerController($this->authCustomer);
    }

    public function getCurrentBalance(){
        return $this->customerController->getAuthCustomerBalance();
    }

    public function showTransactions(){
        printf("\nYour Transactions are -----\n");
        $transactions = $this->transactionController->getTransactions();
        foreach($transactions as $transaction){
            printf("Email - %s, %s - %.2f BDT at %s\n",$this->authCustomer->getEmail(), $transaction->type->value, $transaction->amount, $transaction->date);
        }
        printf("\n");
    }

    public function dipositMoney(){
        $this->transactionController->diposit();
    }

    public function withdrawMoney(){
        $this->transactionController->withdraw();
    }

    public function transferMoney(){
        $this->transactionController->transfer();
    }
    
        
}
