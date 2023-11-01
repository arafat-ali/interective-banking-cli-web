<?php

declare(strict_types=1);
namespace App\Controller\Customer;
use App\Trait\FilehandlerTrait;
use App\Validator\Validator;
use App\Models\Customer\Customer;
use App\Models\Customer\Customers;
use App\Models\Customer\Transaction;
use App\Models\Customer\Transactions;
use App\Enum\TransactionTypeEnum;
use Carbon\Carbon;
use App\Middleware\Auth;
use App\SessionHandle\Session;

class TransactionController{
    use FilehandlerTrait;
    private Transactions $transactions;
    private Customers $customers;
    private Customer $customer;
    private Session $session;


    public function __construct()
    {
        $this->session = new Session();
    }

    public function set(Customer $customer){
        $this->customer = $customer;
        $this->transactions = new Transactions();
        $this->transactions->setTransactionListOfCustomer($this->getItemsFromFile((new Transaction)->getFileName()),$this->customer);
        $this->customers = new Customers();
        $this->customers->setCustomersfromFile($this->getItemsFromFile($this->customer->getFileName()));
    }

    public function getTransactions(){
        return $this->transactions->getList();
    }

    public function diposit(){
        $amount = intval(readline('Please insert amount in BDT: '));
        $date = Carbon::now()->toDateTimeString();

        $result = $this->insertDepositData($this->customer->getId(), (float)$amount, $date);

        if(!$result) echo "\nSomething happened bad!\n\n";
        else echo "\nSuccessfully Deposited\n\n";

    }

    public function getDeposit(){
        if(!Auth::status()) return view('login');
        $authUser = Auth::user();
        $customer = (new Customer)->findByKey('', $authUser["id"]);
        return view('Customer/deposit', ["data" => $customer]);
    }


    public function postDeposit(){
        $this->session->unset("failure");
        $this->session->unset("success");
        if(!Auth::status()) return view('login');
        //Getting Authenticated customer's updated Data from Database and set With Model
        $this->customer = new Customer();
        $customerFromDB = $this->customer->findByKey('', Auth::user()["id"]);
        $this->customer->set($customerFromDB["id"], $customerFromDB["name"], $customerFromDB["email"],$customerFromDB["password"], $customerFromDB["balance"]);

        //Getting Transactions from Files 
        $this->transactions = new Transactions();
        $this->transactions->setTransactionListOfCustomer($this->getItemsFromFile((new Transaction)->getFileName()),$this->customer);
        
        $amount = isset($_POST["amount"]) ? $_POST["amount"] :0;
        $date = Carbon::now()->toDateTimeString();
        
        $result = $this->insertDepositData($this->customer->getId(), (float)$amount, $date);

        if(!$result) $this->session->set("failure", ["msg" => "Deposit operation failed", "time" => time()]);
        else $this->session->set("success", ["msg" => "Deposit successfull", "time" => time()]);

        return view('Customer/deposit', ["data" => $this->customer->get()]);
    }

    private function insertDepositData(int $userId, float $amount, string $date){
        //Inserting Transaction in DB
        $type = TransactionTypeEnum::DIPOSIT;
        $transactionStatus =  (new Transaction)->create([$userId, $type->value, $amount, $date]);
        if(!$transactionStatus) return false;

        //Updating User in DB
        $userUpdateStatus = $this->customer->update((float)$this->customer->getBalance() + $amount, $userId);
        if(!$userUpdateStatus) return false;

        //Insert Into file
        $insertIntoFileStatus = $this->transactionOperation([$userId, $type->value, $amount, $date]);
        $depositSuccess = false;
        if($insertIntoFileStatus){
            $this->balanceUpdateOfCustomer($this->customer->getEmail(), (float)$amount, $type);
            $this->customer->setBalance($this->customer->getBalance() + (float)$amount);
            $depositSuccess=true;
        }
        return $depositSuccess;
    }

    public function withdraw(){
        $amount = intval(readline('Please insert amount in BDT: '));
        $date = Carbon::now()->toDateTimeString();

        //Check if the user have sufficient balance
        if(!(new Validator())->isUserHaveEnounghBalance($amount, $this->customer->getBalance())){
            echo "\nYou don't have enough balance\n\n";
            return;
        }

        $result = $this->insertWithdrawData($this->customer->getId(), (float)$amount, $date);
        if(!$result) echo "\nSomething happened bad!\n\n";
        else echo "\nSuccessful Withdraw\n\n";
    }


    public function getWithdraw(){
        if(!Auth::status()) return view('login');
        $authUser = Auth::user();
        $customer = (new Customer)->findByKey('', $authUser["id"]);
        return view('Customer/withdraw', ["data" => $customer]);
    }

    public function postWithdraw(){
        $this->session->unset("failure");
        $this->session->unset("success");
        if(!Auth::status()) return view('login');
        //Getting Authenticated customer's updated Data from Database and set With Model
        $this->customer = new Customer();
        $customerFromDB = $this->customer->findByKey('', Auth::user()["id"]);
        $this->customer->set($customerFromDB["id"], $customerFromDB["name"], $customerFromDB["email"],$customerFromDB["password"], $customerFromDB["balance"]);

        //Getting Transactions from Files and 
        $this->transactions = new Transactions();
        $this->transactions->setTransactionListOfCustomer($this->getItemsFromFile((new Transaction)->getFileName()),$this->customer);
        
        $amount = isset($_POST["amount"]) ? $_POST["amount"] :0;
        $date = Carbon::now()->toDateTimeString();
        //Check if the user have sufficient balance
        if(!(new Validator())->isUserHaveEnounghBalance((float)$amount, $this->customer->getBalance())){
            $this->session->set("failure", ["message" => "Withdraw operation failed, Insufficient balance", "wtime" => time()]);
            return view('Customer/withdraw', ["data" => $this->customer->get()]);
        }
        
        $result = $this->insertWithdrawData($this->customer->getId(), (float)$amount, $date);

        if(!$result) $this->session->set("failure", ["message" => "Withdraw operation failed", "wtime" => time()]);
        else $this->session->set("success", ["message" => "Withdraw successfull", "wtime" => time()]);

        return view('Customer/withdraw', ["data" => $this->customer->get()]);
    }

    private function insertWithdrawData(int $userId, float $amount, string $date){
        //Inserting Transaction in DB
        $type = TransactionTypeEnum::WITHDRAW;
        $transactionStatus =  (new Transaction)->create([$userId, $type->value, $amount, $date]);
        if(!$transactionStatus) return false;

        //Updating User in DB
        $userUpdateStatus = $this->customer->update((float)$this->customer->getBalance() - $amount, $userId);
        if(!$userUpdateStatus) return false;

        //Insert Into file
        $insertIntoFileStatus = $this->transactionOperation([$userId, $type->value, $amount, $date]);
        $withdrawSuccess = false;
        if($insertIntoFileStatus){
            $this->balanceUpdateOfCustomer($this->customer->getEmail(), (float)$amount, $type);
            $this->customer->setBalance($this->customer->getBalance() - (float)$amount);
            $withdrawSuccess=true;
        }
        return $withdrawSuccess;
    }


    public function transfer(){
        $validator = new Validator();
        $inputEmail = (string) trim(readline('Please insert email: '));
        $email = $validator->getEmailWithValidation($inputEmail);
        $customer = $validator->isUserExist($email, $this->customers->getList());
        if($customer === null){
            printf("\nAccount with this email - %s not exists!\n", $email);
            printf("\nTransaction has failed\n");
            return false;
        }
        //This customer means Logged in customer. Setting up in set function written above, called by CLI
        if($email === $this->customer->getEmail()){
            printf("\nAccount with same account not possible!\n");
            return false;
        }

        $amount = intval(readline('Please insert amount in BDT: '));

        //Check if the user have sufficient balance
        if(!(new Validator())->isUserHaveEnounghBalance($amount, $this->customer->getBalance())){
            echo "\nYou don't have enough balance\n\n";
            return;
        }

        //Transfer Operation
        $transferSuccess = true;
        $date = Carbon::now()->toDateTimeString();

        //Withdraw operation From Auth User
        //$this->customer here is auth user
        $withdrawResult = $this->insertWithdrawData($this->customer->getId(), (float)$amount, $date);
        if(!$withdrawResult){
            $transferSuccess = false;
            echo "\nWithdraw operation failed\n\n";
            return;
        }

        //Deposit operation in Deposit user account
        //$this->customer here is deposit user
        $depositUser = new Customer();
        $depositUserFromDB = $depositUser->findByKey($inputEmail);
        $depositUser->set($depositUserFromDB["id"], $depositUserFromDB["name"], $depositUserFromDB["email"],$depositUserFromDB["password"], $depositUserFromDB["balance"]);
        $this->customer = $depositUser;
        $depositResult = $this->insertDepositData($this->customer->getId(), (float)$amount, $date);

        if(!$depositResult) {
            $transferSuccess = false;
            echo "\Deposit operation failed\n\n";
            return;
        }

        if(!$transferSuccess) echo "\nSomething happened bad!\n\n";
        else echo "\nSuccessfully Transferred\n\n";
    }

    public function getTransfer(){
        if(!Auth::status()) return view('login');
        $authUser = Auth::user();
        $customer = (new Customer)->findByKey('', $authUser["id"]);
        return view('Customer/transfer', ["data" => $customer]);
    }

    public function postTransfer(){
        $validator = new Validator();
        $this->session->unset("failure");
        $this->session->unset("success");
        if(!Auth::status()) return view('login');

        //Getting Authenticated customer's updated Data from Database and set With Model
        $customer = new Customer();
        $customerFromDB = $customer->findByKey('', Auth::user()["id"]);
        $customer->set($customerFromDB["id"], $customerFromDB["name"], $customerFromDB["email"],$customerFromDB["password"], $customerFromDB["balance"]);
        $this->customer = $customer;

        //Getting Transactions from Files 
        $this->transactions = new Transactions();
        $this->transactions->setTransactionListOfCustomer($this->getItemsFromFile((new Transaction)->getFileName()),$this->customer);
        
        //Data from Input
        $inputEmail = isset($_POST["email"]) ? $_POST["email"] : '';
        $amount = isset($_POST["amount"]) ? $_POST["amount"] :0;
        $date = Carbon::now()->toDateTimeString();

        //Email validation
        if($inputEmail == '' || !$validator->getEmailWithValidation($inputEmail)){
            $this->session->set("error", ["email" => "Email must be valid"]);
            return view('customer/transfer', ["data" => $this->customer->get()]);
        }
        
        //Checking if there is avalable account with given email as input
        $depositUser = new Customer();
        $depositUserFromDB = $depositUser->findByKey($inputEmail);
        if(!$depositUserFromDB){
            $this->session->set("error", ["email" => "Account not found with this email!"]);
            return view('customer/transfer', ["data" => $this->customer->get()]);
        }

        //Check if the user who is transferring have sufficient balance
        if(!(new Validator())->isUserHaveEnounghBalance((float)$amount, $this->customer->getBalance())){
            $this->session->set("failure", ["tmessage" => "Withdraw operation failed, Insufficient balance", "ttime" => time()]);
            return view('customer/transfer', ["data" => $this->customer->get()]);
        }
        
        //Withdraw operation From Auth User
        //$this->customer here is auth user
        $withdrawResult = $this->insertWithdrawData($this->customer->getId(), (float)$amount, $date);
        if(!$withdrawResult){
            $this->session->set("failure", ["tmessage" => "Withdraw operation failed", "ttime" => time()]);
            return view('customer/transfer', ["data" => $this->customer->get()]);
        }

        //Deposit operation in Deposit user account
        //$this->customer here is deposit user
        $depositUser->set($depositUserFromDB["id"], $depositUserFromDB["name"], $depositUserFromDB["email"],$depositUserFromDB["password"], $depositUserFromDB["balance"]);
        $this->customer = $depositUser;
        $depositResult = $this->insertDepositData($this->customer->getId(), (float)$amount, $date);

        if(!$depositResult) {
            $this->session->set("failure", ["tmessage" => "Deposit operation failed", "ttime" => time()]);
            return view('customer/transfer', ["data" => $customer->get()]);
        }

        $this->session->set("success", ["tmessage" => "Transfer operation successfull", "ttime" => time()]);
        return view('Customer/transfer', ["data" => $customer->get()]);
    }


    private function transactionOperation(array $input):bool{
        $transactionFromDB = (new Transaction)->findLatest();
        $transaction = new Transaction();
        $insertIntoFileStatus = $this->insertNewItemIntoFile($transaction->getFileName(), [$transactionFromDB["id"], ...$input]);
        if($insertIntoFileStatus){
            $transaction->set($this->customer, TransactionTypeEnum::fromValue($input[1]), $input[2], $input[3]);
            $this->transactions->insertTransactionToList($transaction);
        }
        return $insertIntoFileStatus;
    }

    private function balanceUpdateOfCustomer(String $email, float $updatedAmount, TransactionTypeEnum $type){
        $this->updateBalanceIntoFile($this->customer->getFileName(), $email, $updatedAmount, $type->value);
    }

}