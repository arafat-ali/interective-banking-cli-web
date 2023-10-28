<?php

declare(strict_types=1);

namespace App\Routes;
use App\Routes\Router;
use App\Controller\Customer\AuthController;
use App\Controller\Customer\CustomerController;
use App\Controller\Customer\TransactionController;


Router::get('', function(){
    require_once 'App/Views/home.php';
});

Router::get('login', function(){
    return (new AuthController())->viewLoginPage();
});

Router::post('login', function(){
    return (new AuthController())->postLogin();
});


Router::get('register', function(){
    return (new AuthController())->viewRegisterPage();
});

Router::post('register', function(){
    return (new AuthController())->postRegistration();
});


Router::get('customer/dashboard', function(){
    return (new TransactionController())->dashboard();
});

Router::get('customer/deposit', function(){
    return (new TransactionController())->getDeposit();
});
Router::post('customer/deposit', function(){
    return (new TransactionController())->postDeposit();
});

Router::get('customer/withdraw', function(){
    return (new TransactionController())->getWithdraw();
});

Router::post('customer/withdraw', function(){
    return (new TransactionController())->postWithdraw();
});

Router::get('customer/transfer', function(){
    return (new TransactionController())->transferinWeb();
});