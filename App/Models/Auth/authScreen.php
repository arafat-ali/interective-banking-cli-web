<?php

declare(strict_types=1);
namespace App\Models\Auth;
use App\Controller\Customer\AuthController;
use App\Models\Customer\Customer;

class AuthScreen{

    private AuthController $authController;
    private Customer $customer;
    
    private const LOGIN = 1;
    private const REGISTER = 2;
    private const EXIT = 0;

    private bool $loginSuccess = false;
    public int $choice;


    private array $options = [
        self::LOGIN => 'Login',
        self::REGISTER => 'Register',
        self::EXIT => 'Exit System',
    ];

    public function __construct(){
        $this->authController = New AuthController();
    }

    public function getAuthenticationSuccess():bool{
        return $this->loginSuccess;
    }

    public function getAuthCustomer():Customer{
        return $this->customer;
    }

    public function logoutAuthCustomer(){
        $this->loginSuccess = false;
    }

    public function run(){
        echo "\nWellcome to Interective Sky Banking App!!\n\n";
        while(true){
            foreach ($this->options as $option => $label) {
                printf("Press %d to - %s\n", $option, $label);
            }
            printf("\n");

            $this->choice = intval(readline("Enter your option: "));
            print($this->choice);
            switch ($this->choice) {
                case self::LOGIN:
                    $this->loginSuccess = $this->authController->login();
                    if($this->loginSuccess){
                        $this->customer = $this->authController->getCustomer();
                        return true;
                    }
                    break;
                
                case self::REGISTER:
                    $this->loginSuccess = $this->authController->register();
                    if($this->loginSuccess) {
                        $this->customer = $this->authController->getCustomer();
                        return true;
                    }
                    break;
                
                case self::EXIT:
                    return false;

                default:
                    echo "\nInvalid option.\n";
                    break;
            }
            
        }
    }
}

