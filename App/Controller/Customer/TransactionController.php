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

class TransactionController{
    use FilehandlerTrait;
    private Transactions $transactions;
    private Customers $customers;
    private Customer $customer;

    public function __construct(Customer $customer){
        $this->customer = $customer;
        $this->transactions = new Transactions();
        $this->transactions->setTransactionListOfCustomer($this->getItemsFromFile((new Transaction)->getFileName()),$this->customer);
        $this->customers = new Customers($this->getItemsFromFile($this->customer->getFileName()));
    }

    public function getTransactions(){
        return $this->transactions->getList();
    }

    public function diposit(){
        $type = TransactionTypeEnum::DIPOSIT;
        $amount = intval(readline('Please insert amount in BDT: '));
        $date = Carbon::now()->toDateTimeString();

        //Inserting into File
        $insertIntoFileStatus = $this->transactionOperation([$type->value, $amount, $date]);
        $depositSuccess = false;
        if($insertIntoFileStatus){
            //Updating Balance in file storage
            $this->balanceUpdateOfCustomer($this->customer->getEmail(), (float)$amount, $type);
            $this->customer->setBalance($this->customer->getBalance() + (float)$amount);
            $depositSuccess=true;
        }

        if(!$depositSuccess) echo "\nSomething happened bad!\n\n";
        else echo "\nSuccessfully Deposited\n\n";

    }

    public function withdraw(){
        $type = TransactionTypeEnum::WITHDRAW;
        $amount = intval(readline('Please insert amount in BDT: '));
        $date = Carbon::now()->toDateTimeString();

        //Check if the user have sufficient balance
        if(!(new Validator())->isUserHaveEnounghBalance($amount, $this->customer->getBalance())){
            echo "\nYou don't have enough balance\n\n";
            return;
        }

        //Inserting into File
        $insertIntoFileStatus = $this->transactionOperation([$type->value, $amount, $date]);
        $withdrawSuccess = false;
        if($insertIntoFileStatus){
            //Updating Balance in file storage
            $this->balanceUpdateOfCustomer($this->customer->getEmail(), (float) $amount, $type);
            $this->customer->setBalance($this->customer->getBalance() - (float)$amount);
            $withdrawSuccess=true;
        }

        if(!$withdrawSuccess) echo "\nSomething happened bad!\n\n";
        else echo "\nSuccessful Withdraw\n\n";
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
        $transaction = new Transaction();
        $insertIntoFileStatus = $this->insertNewItemIntoFile($transaction->getFileName(), [$this->customer->getEmail(), ...$input]);
        if($insertIntoFileStatus){
            $transaction->setTransaction($this->customer, TransactionTypeEnum::fromValue($input[0]), $input[1], $input[2]);
            $this->transactions->insertTransactionToList($transaction);
        }
        return $insertIntoFileStatus;
    }

    private function balanceUpdateOfCustomer(String $email, float $updatedAmount, TransactionTypeEnum $type){
        $this->updateBalanceIntoFile($this->customer->getFileName(), $email, $updatedAmount, $type->value);
    }


}