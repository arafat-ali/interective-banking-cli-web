<?php

declare(strict_types=1);
namespace App\Controller\Customer;
use App\Models\Customer\Customer;
use App\Models\Customer\Transactions;
use App\Trait\FilehandlerTrait;
use App\Helpers\Helper;
use App\SessionHandle\Session;
use App\Middleware\Auth;

class CustomerController {
    use FilehandlerTrait;
    private Customer $authcustomer;
    private Session $session;
    private Transactions $transactions;
    public function __construct(){
        $this->session = new Session();
        $this->transactions = new Transactions();
    }

    public function set(Customer $customer){
        $this->authcustomer = $customer;
    }

    public function getAuthCustomerBalance(){
        return $this->authcustomer->getBalance();
    }

    public function dashboard(){
        if(!Auth::status()){
            return view('login');
        }
        $this->authcustomer = new Customer();
        $customerFromDB = $this->authcustomer->findByKey('', Auth::user()["id"]);
        $this->authcustomer->set($customerFromDB["id"], $customerFromDB["name"], $customerFromDB["email"],$customerFromDB["password"], $customerFromDB["balance"]);
        $transactions = $this->transactions->listByCustomer($this->authcustomer->getId());
        return view('Customer/dashboard', ["data"=>$transactions, "user"=>$this->authcustomer->get()]);
    }

}