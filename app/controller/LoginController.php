<?php
namespace App\controller ;
use App\service\LoginService ;
// require_once($root.'app/service/LoginService.php');
class LoginController {
    private $_login ;
    public function __construct() {
       $this->_login = LoginService::GetInstance();
    }
    


    // public function register ($request, $response, $args)
    // {
    //     global $apiResult ;


    //     $parsedBody = $request->getParsedBody();
    //     LoginService::Login();

    //     $con = Database::getInstance();
    //     $sql = "SELECT id, name, email FROM users" ;
    //     $stmt = $con->dbh->prepare($sql);
    //     if($stmt->execute()){
    //             $row = $stmt->fetchAll(PDO::FETCH_ASSOC);
    //     }else{
    //             $error = $stmt->errorInfo ();
    //             $result ["result"] = false;
    //             $result ["error"] = $stmt->errorCode () . " " . $error [2];
    //     }
    //     var_dump($row);die();
        
    // } 
    public function loginGuid ($request, $response, $args)
    {
        global $apiResult ;
        $parsedBody = $request->getParsedBody();
        if(isset($parsedBody['guid'])&&!empty($parsedBody['guid'])){
            $login = $this->_login->loginGuid($parsedBody,true);
            $apiResult = array_merge($apiResult,$login);
        }else{
            $regis = $this->_login->registerGuid();
            $apiResult = array_merge($apiResult,$regis);
        }
    } 
    public function loginFB ($request, $response, $args)
    {
        global $apiResult ;
        $parsedBody = $request->getParsedBody();
        if(isset($parsedBody['guid'])&&!empty($parsedBody['guid'])){
            $login = $this->_login->loginGuid($parsedBody,true);
            $apiResult = array_merge($apiResult,$login);
        }elseif( isset($parsedBody['facebook_id'])&&!empty($parsedBody['facebook_id'])
            &&isset($parsedBody['name']) &&!empty($parsedBody['name'])
            &&isset($parsedBody['email'])&&!empty($parsedBody['email'])){
            $regis = $this->_login->registerFB($parsedBody);
            $apiResult = array_merge($apiResult,$regis);
        }
    }
}