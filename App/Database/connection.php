<?php

declare(strict_types=1);

namespace App\Database;
use PDO;

class Connection{
    private $db_host = 'localhost';
    private $db_name = 'banking-web';
    private $db_user = 'root';
    private $db_password = '';

    public PDO $pdo;
    public function __construct()
    {
        try{
            $this->pdo = new PDO("mysql:host={$this->db_host}; dbname={$this->db_name};", $this->db_user, $this->db_password);
        
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        
            $this->pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        }
        catch(\PDOException $e){
            die("Database connection failed: {$e->getMessage()}");
        }
    }

    
}


