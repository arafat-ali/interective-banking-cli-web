<?php

namespace App\Validator;
use App\Models\Customer\Customer;
use App\Models\Admin\Admin;
class Validator{

    public function getEmailWithValidation(){
        $inputEmail = (string) trim(readline('Please insert your email: '));
        if (filter_var($inputEmail, FILTER_VALIDATE_EMAIL)) return strtolower($inputEmail);
        else {
            echo "\nInvalid Email!\n";
            return $this->getEmailWithValidation();
        }

    }

    public function getPasswordWithValidation(){
        $inputPassword = (string) readline('Please insert your password: ');
        if (strlen($inputPassword)>=6) return $inputPassword;
        else {
            echo "\nPassword minimum length must be 6!\n";
            return $this->getPasswordWithValidation();
        }

    }

    //Replace isUserAlreadyRegisterred    
    public function isUserExist($email, $list):Customer|Admin|null{
        $userInfo = null;
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