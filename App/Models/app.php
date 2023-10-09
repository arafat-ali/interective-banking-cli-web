<?php
declare(strict_types=1);
namespace App\Models;

use App\Models\Auth\AuthScreen;
use App\Models\Customer\Customer;
use App\Models\SkyApp\CustomerDashboard;
class App{

    private AuthScreen $authScreen;
    private Customer $authCustomer;

    private const SHOW_CURRENT_BALANCE = 1;
    private const SHOW_TRANSACTION = 2;
    private const DEPOSITE_MONEY = 3;
    private const WITHDRAW_MONEY = 4;
    private const TRANSFER_MONEY = 5;
    private const LOGOUT = 6;


    private array $options = [
        self::SHOW_CURRENT_BALANCE => 'Show Current Balance',
        self::SHOW_TRANSACTION => 'Show Transactions',
        self::DEPOSITE_MONEY => 'Deposit Money',
        self::WITHDRAW_MONEY => 'Withdraw Money',
        self::TRANSFER_MONEY => 'Transfer Money',
        self::LOGOUT => 'Logout',
    ];

    public function __construct(){
        $this->authScreen = New AuthScreen();
        //$this->skyApp = New CustomerDashboard(new FileStorage());
    }

    public function run(){
        if(!$this->authScreen->run()) {
            echo "\nApplication is Exited!\n";
            return;
        }
        $this->authCustomer = $this->authScreen->getAuthCustomer();
        printf("\nWellcome %s\n\n", $this->authCustomer->getName());
        
        while($this->authScreen->getAuthenticationSuccess() ){
            foreach ($this->options as $option => $label) {
                printf("Press %d to - %s\n", $option, $label);
            }

            $choice = intval(readline("Enter your option: "));
            switch ($choice) {
                case self::SHOW_CURRENT_BALANCE:
                    printf("\nYour Current Balance is %.2f Taka\n\n", (new CustomerDashboard($this->authCustomer))->getCurrentBalance());
                    break;

                case self::SHOW_TRANSACTION:
                    (new CustomerDashboard($this->authCustomer))->showTransactions();
                    break;

                case self::DEPOSITE_MONEY:
                    (new CustomerDashboard($this->authCustomer))->dipositMoney();
                    break;
                
                case self::WITHDRAW_MONEY:
                    (new CustomerDashboard($this->authCustomer))->withdrawMoney();
                    break;

                case self::TRANSFER_MONEY:
                    (new CustomerDashboard($this->authCustomer))->transferMoney();
                    break;

                case self::LOGOUT:
                    $this->authScreen->logoutAuthCustomer();
                    $this->run();
                    break;
                default:
                    echo "Invalid option.\n";
            }
            
        }
    }
}