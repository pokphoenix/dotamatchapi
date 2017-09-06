<?php
namespace API ;
use PDO ;
use PDOException;
class Database {
    public $dbh;
    public $error;
    private static $instance;

    private function __construct() {
        $root = str_replace('src', '', realpath(__DIR__ )) ;
        require_once($root.'config/database.php');
        try{
            $dbh = new PDO("mysql:host=".$config['db']['host'].";dbname=".$config['db']['dbname'], $config['db']['user'] , $config['db']['pass']);  
            $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $dbh->exec("set names utf8");
            $this->dbh = $dbh ;
        }
        // Catch any errors
        catch(PDOException $e){
            echo 'Connection failed: ' . $e->getMessage();
            exit;
        }
        
    }

    public static function getInstance() {
        if (!isset(self::$instance))
        {
            $object = __CLASS__;
            self::$instance = new $object;
        }
        return self::$instance;
    }
    
    
}

?>