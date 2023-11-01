<?php

declare(strict_types=1);

namespace App\Models\Customer;
use App\Models\Customer\Transaction;
use App\Models\Customer\Customer;
use App\Models\Customer\Customers;
use App\Enum\TransactionTypeEnum;
use App\Database\Connection;

class Transactions{
    private array $transactionList;
    private array $customers;
    private string $tableName = 'transactions';
    private string $usersTable = 'users';

    public function setTransactionListOfCustomer(array $list, Customer $user){
        foreach($list as $row){
            if((int)$row[1] != $user->getId()) continue;
            $transaction = new Transaction();
            $transaction->set($user, TransactionTypeEnum::fromValue($row[2]), (float)$row[3], $row[4]);
            $this->insertTransactionToList($transaction);
        }
    }

    public function setTransactionListOfAllUser(array $list, array $customers){
        $this->customers = $customers;
        foreach($list as $row){
            $user = $this->getCustomerByEmail($row[0]);
            $transaction = new Transaction();
            $transaction->set($user, TransactionTypeEnum::fromValue($row[1]), (float)$row[2],$row[3]);
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

    public function listByCustomer(int $id=null):array|bool{
        try{
            $pdo = (new Connection())->pdo;
            $stmt = $pdo->prepare("SELECT name, email, transactions.* FROM {$this->usersTable} JOIN {$this->tableName} on {$this->usersTable}.id = {$this->tableName}.userId  WHERE userId =:id");
            $stmt->execute(['id'=>$id]);
            $transactions = $stmt->fetchAll();
            return $transactions;
        }
        catch(\PDOException $e){
            return false;
            die("Something happend wrong: {$e->getMessage()}");
        }
    }

}
