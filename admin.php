<?php

require "vendor/autoload.php";
use Carbon\Carbon;
use App\Models\AdminApp;

$app = new AdminApp();

$app->run();
