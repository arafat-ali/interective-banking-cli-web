<?php

declare(strict_types=1);
namespace App\Controller\Admin;
use App\Models\Admin\Admin;
use App\Models\Admin\Admins;
use App\Trait\FilehandlerTrait;
use App\Validator\Validator;

class AuthController {
    use FilehandlerTrait;
    private Admins $admins;

    private Admin $admin;
    private Validator $validator;

    public function __construct()
    {
        $this->admin = new Admin();
        $this->validator = new Validator();
        $this->admins = new Admins($this->getItemsFromFile($this->admin->getFileName()));
    }

    public function getAdmin():Admin{
        return $this->admin;
    }


    public function Login():bool{
        $email = $this->validator->getEmailWithValidation();
        $password = $this->validator->getPasswordWithValidation();

        $admin = $this->validator->isUserExist($email, $this->admins->getList());
        if($admin === null){
            echo "Account not found!\n\n";
            return false;
        }

        if($admin->getPassword() !== md5($password)){
            echo "\nInvalid credential!\n\n";
            return false;
        }
        $this->admin = $admin;
        return true;
    }


    public function register():bool{
        $name = (string) trim(readline('Please insert your name: '));
        $email = $this->validator->getEmailWithValidation();
        $password = $this->validator->getPasswordWithValidation();

        //Check if any admin with this email is available
        if($this->validator->isUserExist($email, $this->admins->getList()) !==null){
            echo "Account already available with this email!";
            return false;
        }

        $registerSuccess = false;
        //Insert data into file
        $insertIntoFileStatus = $this->insertNewItemIntoFile($this->admin->getFileName(), [$name, $email, md5($password)]);

        if($insertIntoFileStatus){
            $this->admin->setAdmin($name, $email, md5($password));
            $this->admins->insertAdminToList($this->admin);
            $registerSuccess = true;
        }

        if(!$registerSuccess) {
            echo "\nSomething happened bad!\n\n";
            return false;
        }
        echo "\nSuccessfully Registerred\n\n";
        return $registerSuccess;
    }

}