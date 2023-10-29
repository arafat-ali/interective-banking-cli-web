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
        $this->customers = new Customers($this->getItemsFromFile($this->customer->getFileName()));
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

        //Getting Transactions from Files and 
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
        $insertIntoFileStatus = $this->transactionOperation([$type->value, $amount, $date]);
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
        $insertIntoFileStatus = $this->transactionOperation([$type->value, $amount, $date]);
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
        $email = $validator->getEmailWithValidation();

        $customer = $validator->isUserExist($email, $this->customers->getList());
        if($customer === null){
            printf("\nAccount with this email - %s not exists!\n", $email);
            printf("\nTransaction has failed\n");
            return false;
        }

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
        $transferSuccess = false;
        $date = Carbon::now()->toDateTimeString();

        $withdrawStatus = $this->transactionOperation([TransactionTypeEnum::WITHDRAW->value, $amount, $date]);
        $dipositStatus = $this->transactionOperation([TransactionTypeEnum::DIPOSIT->value, $amount, $date]);

        if($withdrawStatus && $dipositStatus){
            //Withdraw Balance update
            $this->balanceUpdateOfCustomer($this->customer->getEmail(), (float) $amount, TransactionTypeEnum::WITHDRAW);
            $this->customer->setBalance($this->customer->getBalance() - (float)$amount);

            //Diposit Balance Update
            $this->balanceUpdateOfCustomer($email, (float) $amount, TransactionTypeEnum::DIPOSIT);
            $transferSuccess=true;
        }

        if(!$transferSuccess) echo "\nSomething happened bad!\n\n";
        else echo "\nSuccessfully Transferred\n\n";
    }


    private function transactionOperation(array $input):bool{
        $transactionFromDB = (new Transaction)->findLatest();
        $transaction = new Transaction();
        $insertIntoFileStatus = $this->insertNewItemIntoFile($transaction->getFileName(), [$transactionFromDB["id"], ...$input]);
        if($insertIntoFileStatus){
            $transaction->set($this->customer, TransactionTypeEnum::fromValue($input[0]), $input[1], $input[2]);
            $this->transactions->insertTransactionToList($transaction);
        }
        return $insertIntoFileStatus;
    }

    private function balanceUpdateOfCustomer(String $email, float $updatedAmount, TransactionTypeEnum $type){
        $this->updateBalanceIntoFile($this->customer->getFileName(), $email, $updatedAmount, $type->value);
    }


    public function dashboard(){
        if(!Auth::status()){
            return view('login');
        }
        return view('Customer/dashboard');
    }

    public function withdrawinWeb(){
        if(!Auth::status()){
            return view('login');
        }
        return view('Customer/withdraw');
    }

    public function getTransfer(){
        if(!Auth::status()){
            return view('login');
        }
        return view('Customer/transfer');
    }


}