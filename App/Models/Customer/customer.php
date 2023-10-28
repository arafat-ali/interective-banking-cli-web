<?php

declare(strict_types=1);
namespace App\Models\Customer;
use App\Database\Connection;
class Customer{
    private int $id;
    private string $name;
    private string $email;
    private string $password;
    private float $balance;

    private string $filename = 'customers.csv';
    private string $tableName = 'users';

    public function set(int $id, String $name, String $email, String $password, float $balance){
        $this->id = $id;
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;
        $this->balance = $balance;
    }


    public function get(){
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
            'balance' => $this->balance
        ];
    }

    public function getName(){
        return $this->name;
    }

    public function getId(){
        return $this->id;
    }

    public function getEmail(){
        return $this->email;
    }

    public function getPassword(){
        return $this->password;
    }

    public function setBalance(float $balance){
        $this->balance = $balance;
    }

    public function getBalance(){
        return $this->balance;
    }

    public function getFileName(){
        return $this->filename;
    }


    public function create(array $data){
        try{
            $pdo = (new Connection())->pdo;
            $stmt = $pdo->prepare("insert into {$this->tableName} (name, email, password, balance) values(?,?,?,?)");
            $result = $stmt->execute($data);
            return $result;
        }
        catch(\PDOException $e){
            die("Something happend wrong: {$e->getMessage()}");
        }

    }

    public function findByKey(string $email=null, int $id=null):array|bool{
        try{
            $pdo = (new Connection())->pdo;
            $stmt = $pdo->prepare("SELECT * FROM {$this->tableName} WHERE id =:id OR email=:email limit 1");
            $stmt->execute(['id'=>$id, 'email'=>$email]);
            $user = $stmt->fetch();
            return $user;
        }
        catch(\PDOException $e){
            return false;
            die("Something happend wrong: {$e->getMessage()}");
        }
    }

    public function update(float $balance, int $id){
        try{
            $pdo = (new Connection())->pdo;
            $stmt = $pdo->prepare("UPDATE {$this->tableName} SET balance=? WHERE id=?");
            $result = $stmt->execute([$balance, (int)$id]);
            return $result;
        }
        catch(\PDOException $e){
            die("Something happend wrong: {$e->getMessage()}");
        }

    }


}