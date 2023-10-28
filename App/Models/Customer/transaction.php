<?php

declare(strict_types=1);
namespace App\Models\Customer;
use App\Models\Customer\Customer;
use App\Enum\TransactionTypeEnum;
use App\Database\Connection;

class Transaction{
    public Customer $customer;
    public TransactionTypeEnum $type;
    public float $amount;
    public string $date;
    private string $filename = 'transactions.csv';
    private string $tableName = 'transactions';


    public function set(Customer $customer, TransactionTypeEnum $type, float $amount, string $date){
        $this->customer = $customer;
        $this->type = $type;
        $this->amount = $amount;
        $this->date = $date;
        // 01619062324
    }

    public function create(array $data){
        try{
            $pdo = (new Connection())->pdo;
            $stmt = $pdo->prepare("insert into {$this->tableName} (userId, type, amount, date) values(?,?,?,?)");
            $result = $stmt->execute($data);
            return $result;
        }
        catch(\PDOException $e){
            die("Something happend wrong: {$e->getMessage()}");
        }

    }

    public function findLatest():array|bool{
        try{
            $pdo = (new Connection())->pdo;
            $stmt = $pdo->prepare("SELECT * FROM {$this->tableName} ORDER BY id DESC LIMIT 1");
            $stmt->execute();
            $user = $stmt->fetch();
            return $user;
        }
        catch(\PDOException $e){
            die("Something happend wrong: {$e->getMessage()}");
        }
    }

    public function getFileName(){
        return $this->filename;
    }

}