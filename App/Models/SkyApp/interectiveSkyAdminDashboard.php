<?php
declare(strict_types=1);

namespace App\Models\SkyApp;
use App\Models\Admin\Admin;
use App\Controller\Admin\UserReportController;

class InterectiveSkyAdminDashboard{
    private Admin $authAdmin;
    private UserReportController $userReportController;
    

    public function __construct(Admin $admin){
        $this->authAdmin = $admin;
        $this->userReportController = new UserReportController($this->authAdmin);
    }


    public function showAllUser(){
        printf("\nAll Customers of Interective Banking are--\n\n");
        $this->userReportController->getAllCustomer();
        printf("\n");
    }

    public function showTransactionsOfAllUser(){
        printf("\nTransactions of All Customer--\n\n");
        $this->userReportController->getAllUserTransactions();
        printf("\n");
    }

    public function showTransactionsOfSpecificUser(){
        $this->userReportController->getTransactionsOfSpecificUser();
        printf("\n\n");
    }
    
        
}
