<?php

declare(strict_types=1);
namespace App\Models\Admin;

class Admin {
    private string $name;
    private string $email;
    private string $password;

    private string $filename = 'admin.csv';

    public function setAdmin(String $name, String $email, String $password){
        $this->name = $name;
        $this->email = $email;
        $this->password = $password;
    }


    public function getAdmin(){
        return [
            'name' => $this->name,
            'email' => $this->email,
            'password' => $this->password
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

    public function getFileName(){
        return $this->filename;
    }



}