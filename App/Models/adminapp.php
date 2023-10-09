<?php
declare(strict_types=1);
namespace App\Models;

use App\Models\Auth\AuthAdminScreen;
use App\Models\Admin\Admin;
use App\Models\SkyApp\InterectiveSkyAdminDashboard;
class AdminApp{

    private AuthAdminScreen $authAdminScreen;
    private Admin $admin;

    private const SHOW_ALL_CUSTOMER = 1;
    private const SHOW_All_TRANSACTION = 2;
    private const SHOW_SPECIFIC_USER_TRANSACTION = 3;
    private const LOGOUT = 4;


    private array $options = [
        self::SHOW_ALL_CUSTOMER => 'Show All Customer',
        self::SHOW_All_TRANSACTION => 'Show All Transactions',
        self::SHOW_SPECIFIC_USER_TRANSACTION => 'Show Specific User Transaction',
        self::LOGOUT => 'Logout',
    ];

    public function __construct(){
        $this->authAdminScreen = New AuthAdminScreen();
    }

    public function run(){
        if(!$this->authAdminScreen->run()){
            echo "\nApplication is Exited!\n";
            return;
        }
        $this->admin = $this->authAdminScreen->getAuthAdmin();
        printf("\nWellcome %s\n\n", $this->admin->getName());
        
        while($this->authAdminScreen->getAuthenticationSuccess()){
            foreach ($this->options as $option => $label) {
                printf("Press %d to - %s\n", $option, $label);
            }

            $choice = intval(readline("Enter your option: "));
            switch ($choice) {
                case self::SHOW_ALL_CUSTOMER:
                    (new InterectiveSkyAdminDashboard($this->admin))->showAllUser();
                    break;

                case self::SHOW_All_TRANSACTION:
                    (new InterectiveSkyAdminDashboard($this->admin))->showTransactionsOfAllUser();
                    break;

                case self::SHOW_SPECIFIC_USER_TRANSACTION:
                    (new InterectiveSkyAdminDashboard($this->admin))->showTransactionsOfSpecificUser();
                    break;

                case self::LOGOUT:
                    $this->authAdminScreen->logoutAuthCustomer();
                    $this->run();
                    break;
                default:
                    echo "Invalid option.\n";
            }
            
        }
    }
}