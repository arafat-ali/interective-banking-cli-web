<?php

declare(strict_types=1);
namespace App\Models\Auth;
use App\Controller\Admin\AuthController;
use App\Models\Admin\Admin;

class AuthAdminScreen{

    private AuthController $authController;
    private Admin $admin;
    
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

    public function getAuthAdmin():Admin{
        return $this->admin;
    }

    public function logoutAuthCustomer(){
        $this->loginSuccess = false;
    }

    public function run(){
        echo "\nWellcome to Interective Sky Banking App - Admin Panel!!\n\n";
        while(true){
            foreach ($this->options as $option => $label) {
                printf("Press %d to - %s\n", $option, $label);
            }
            printf("\n");

            $this->choice = intval(readline("Enter your option: "));
            switch ($this->choice) {
                case self::LOGIN:
                    $this->loginSuccess = $this->authController->login();
                    if($this->loginSuccess){
                        $this->admin = $this->authController->getAdmin();
                        return true;
                    }
                    break;
                
                case self::REGISTER:
                    $this->loginSuccess = $this->authController->register();
                    if($this->loginSuccess) {
                        $this->admin = $this->authController->getAdmin();
                        return true;
                    }
                    break;
                
                case self::EXIT:
                    return false;

                default:
                    echo "Invalid option.\n";
                    break;
            }
            
        }
    }
}

