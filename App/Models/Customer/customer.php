<?php

declare(strict_types=1);
namespace App\Models\Customer;

class Customer {
    private string $name;
    private string $email;
    private string $password;
    private float $balance;

    private string $filename = 'customers.csv';

    public function setCustomer(String $name, String $email, String $password, float $balance){
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;
        $this->balance = $balance;
    }


    public function getCustomer(){
        return [
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password,
            'balance' => $this->balance
        ];
    }

    public function getName(){
        return $this->name;
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



}