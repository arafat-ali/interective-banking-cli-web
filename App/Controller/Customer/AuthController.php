<?php

declare(strict_types=1);
namespace App\Controller\Customer;

use App\Models\Customer\Customers;
use App\Models\Customer\Customer;
use App\Validator\Validator;
use App\Trait\FilehandlerTrait;
use App\SessionHandle\Session;
use App\Helpers\Helper;
use App\Database\Operation;
class AuthController {
    use FilehandlerTrait;
    private Customers $customers;
    private Customer $customer;
    private Validator $validator;
    private Session $session;

    public function __construct()
    {
        $this->session = new Session();
        $this->validator = new Validator();
        $this->customer = new Customer();
        $this->customers = new Customers();
        $this->customers->setCustomersfromFile($this->getItemsFromFile($this->customer->getFileName()));
    }


    public function getCustomer():Customer{
        return $this->customer;
    }


    public function Login():bool{
        $inputEmail = (string) trim(readline('Please insert your email: '));
        $email = $this->validator->getEmailWithValidation($inputEmail);
        if(!$email){
            echo "\nInvalid Email!\n";
            return false;
        }

        $inputPassword = (string) readline('Please insert your password: ');
        $password = $this->validator->getPasswordWithValidation($inputPassword);
        if(!$password){
            echo "\nPassword minimum length must be 6!\n";
            return false;
        }
        $this->customer = new Customer();
        $customerFromDB = $this->customer->findByKey($email);

        if($customerFromDB === null){
            echo "Account not found!\n\n";
            return false;
        }

        if($customerFromDB["password"] !== md5($password)){
            echo "\nInvalid credential!\n\n";
            return false;
        }
        $this->customer->set($customerFromDB["id"], $customerFromDB["name"], $customerFromDB["email"],$customerFromDB["password"], $customerFromDB["balance"]);
        return true;
    }


    public function register():bool{
        $name = (string) trim(readline('Please insert your name: '));
        $inputEmail = (string) trim(readline('Please insert your email: '));
        $email = $this->validator->getEmailWithValidation($inputEmail);
        if(!$email){
            echo "\nInvalid Email!\n";
            return false;
        }

        if((new Customer)->findByKey($inputEmail)){
            echo "Account already available with this email!";
            return false;
        }

        $inputPassword = (string) readline('Please insert your password: ');
        $password = $this->validator->getPasswordWithValidation($inputPassword);
        if(!$password){
            echo "\nPassword minimum length must be 6!\n";
            return false;
        } 
        $registerSuccess = $this->insertRegisterData($name, $inputEmail, $inputPassword);
        if(!$registerSuccess) {
            echo "\nSomething happened bad!\n\n";
            return false;
        }
        echo "\nSuccessfully Registerred\n\n";
        return true;
    }


    

    public function viewRegisterPage(){
        return view("register");
    }

    public function postRegistration(){
        $this->session->unset('error');
        $name = isset($_POST["name"]) ? $_POST["name"] : '';
        $inputEmail = isset($_POST["email"]) ? $_POST["email"] : '';
        $inputpassword = isset($_POST["password"]) ? $_POST["password"] : '';

        if($name == ''){
            $this->session->set("error", ["name" => "Name cannot be null"]);
            return header("Location:register");
        }

        if($inputEmail == '' || !$this->validator->getEmailWithValidation($inputEmail)){
            $this->session->set("error", ["email" => "Email must be valid"]);
            return header("Location:register");
        }
        
        $user = (new Customer)->findByKey($inputEmail);
        if($user){
            $this->session->set("error", ["email" => "Account already available with this email!"]);
            return header("Location:register");
        }

        if($inputpassword == '' || !$this->validator->getPasswordWithValidation($inputpassword)){
            $this->session->set("error", ["password" => "Password minimum length must be 6!"]);
            return header("Location:register");
        }
        
        $registerSuccess = $this->insertRegisterData($name,$inputEmail,$inputpassword);
        if(!$registerSuccess){
            $this->session->set("failure", ["msg" => "Registration failed"]);
            return header("Location:register");
        }
        else{
            $this->session->set("success", ["msg" => "Registration successfull"]);
            return header("Location:login");
        }
        
        
    }


    private function insertRegisterData(string $name, string $email, string $password){
        //Inserting in DB
        $dbStatus =  (new Customer)->create([$name, $email, md5($password), 0]);
        if(!$dbStatus) return false;

        $user = (new Customer)->findByKey($email);
        if(!$user) return false;

        $this->customer->set((int)$user['id'], $name, $email, md5($password), 0);
        //Insert into file
        $registerSuccess = false;
        $insertIntoFileStatus = $this->insertNewItemIntoFile($this->customer->getFileName(), $this->customer->get());
        if($insertIntoFileStatus){
            $this->customers->insertCustomerToList($this->customer);
            $registerSuccess = true;
        }
        return $registerSuccess;
    }


    public function viewLoginPage(){
        return view("login");
    }


    public function postLogin(){
        $this->session->unset("error");
        $this->session->unset("auth");
        $inputEmail = isset($_POST["email"]) ? $_POST["email"] : '';
        $inputpassword = isset($_POST["password"]) ? $_POST["password"] : '';

        if($inputEmail == '' || !$this->validator->getEmailWithValidation($inputEmail)){
            $this->session->set("error", ["email" => "Email must be valid"]);
            header("Location:login");
        }

        if($inputpassword == '' || !$this->validator->getPasswordWithValidation($inputpassword)){
            $this->session->set("error", ["password" => "Password minimum length must be 6!"]);
            header("Location:login");
        }

        $customer = (new Customer)->findByKey($inputEmail);
        if(!$customer){
            $this->session->set("error", ["email" => "Account not found"]);
            header("Location:login");
        }

        if($customer[3] !== md5($inputpassword)){
            $this->session->set("error", ["password" => "Password invalid"]);
            header("Location:login");
        }

        $this->session->set("auth", ["user" => $customer]);
        header("Location:customer/dashboard");
    }

}