<?php

declare(strict_types=1);

namespace App\Models\Customer;
use App\Models\Customer\Transaction;
use App\Models\Customer\Customer;
use App\Models\Customer\Customers;
use App\Enum\TransactionTypeEnum;

class Transactions{
    private array $transactionList;
    private array $customers;

    public function __construct()
    {
        
        
    }

    public function setTransactionListOfCustomer(array $list, Customer $user){
        foreach($list as $row){
            if(strtolower((string)$row[0]) != $user->getEmail()) continue;
            $transaction = new Transaction();
            $transaction->setTransaction($user, TransactionTypeEnum::fromValue($row[1]), (float)$row[2],$row[3]);
            $this->insertTransactionToList($transaction);
        }
    }

    public function setTransactionListOfAllUser(array $list, array $customers){
        $this->customers = $customers;
        foreach($list as $row){
            $user = $this->getCustomerByEmail($row[0]);
            $transaction = new Transaction();
            $transaction->setTransaction($user, TransactionTypeEnum::fromValue($row[1]), (float)$row[2],$row[3]);
            $this->insertTransactionToList($transaction);
        }
    }

    public function insertTransactionToList(Transaction $transaction){
        $this->transactionList[]= $transaction;
    }

    public function getList(){
        return $this->transactionList;
    }

    private function getCustomerByEmail(string $email):Customer{
        foreach($this->customers as $customer){
            if($customer->getEmail() === $email){
                return $customer;
            }
        }
        
    }

}
