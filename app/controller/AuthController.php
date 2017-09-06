<?php
namespace App\controller ;
use API\Database;

class AuthController {

//     public function __construct() {
//         $this->userService = UserService::getInstance ();
//     }
    
    public function index ($request, $response, $args)
    {
        $con = Database::getInstance();
        $sql = "SELECT id, name, email FROM users" ;
        $stmt = $con->dbh->prepare($sql);
        if($stmt->execute()){
                $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }else{
                $error = $stmt->errorInfo ();
                $result ["result"] = false;
                $result ["error"] = $stmt->errorCode () . " " . $error [2];
        }
      
        
    }

    public function register ($request, $response, $args)
    {
        $con = Database::getInstance();
        $sql = "SELECT id, name, email FROM users" ;
        $stmt = $con->dbh->prepare($sql);
        if($stmt->execute()){
                $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
        }else{
                $error = $stmt->errorInfo ();
                $result ["result"] = false;
                $result ["error"] = $stmt->errorCode () . " " . $error [2];
        }
        var_dump($row);die();
        
    }
}