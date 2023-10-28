<?php

namespace App\Validator;
use App\Models\Customer\Customer;
use App\Models\Admin\Admin;
class Validator{

    public function getEmailWithValidation(string $inputEmail) :string|bool{
        if (filter_var($inputEmail, FILTER_VALIDATE_EMAIL)) 
            return strtolower($inputEmail)??false;

    }

    public function getPasswordWithValidation(string $inputPassword){
        if (strlen($inputPassword)>=6) return $inputPassword??false;

    }

    //Replace isUserAlreadyRegisterred    
    public function isUserExist($email, $list):Customer|Admin|bool{
        $userInfo = false;
        foreach($list as $user){
            if($user->getEmail()===$email){
                $userInfo = $user;
                break;
            }
        }
        return $userInfo;
    }


    public function isUserHaveEnounghBalance(float $amount, float $balance){
        return $balance>=$amount ? true : false;
    }

}