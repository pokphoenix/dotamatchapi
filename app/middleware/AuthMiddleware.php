<?php
namespace App\middleware ;

use API\Database;
use PDO;
use PDOException;
class AuthMiddleware
{
   
    public static $auth ;

    public function __invoke($request, $response, $next)
    {
        global $apiResult ;
        $routeParams = $request->getAttribute('routeInfo')[2];
        $auth = self::auth($routeParams['token']);
        if(!$auth['result']){
            $apiResult = array_merge($apiResult,$auth);
            return $response;
        }
        $response = $next($request, $response);
        return $response ;
    }

    private static function auth($token){
        $con = Database::getInstance();
        $sql = "SELECT  id
                ,CASE WHEN token_expire < UTC_TIMESTAMP() THEN true 
                ELSE false END as refresh_token  
                FROM users 
                WHERE token = :token LIMIT 1 " ;
        $stmt = $con->dbh->prepare($sql);
        $stmt->bindParam(':token',$token,PDO::PARAM_STR,40);
        try {
            if($stmt->execute()){
                $query = $stmt->fetch(PDO::FETCH_ASSOC);
                if (empty($query)){
                    $result ["result"] = false;
                    $result ["error"] = 'token mismatch';
                }else{
                    $result ["result"] = true;
                    $result ["error"] = '';
                    self::$auth = $query;
                }

               
            }else{
                $error = $stmt->errorInfo ();
                $result ["result"] = false;
                $result ["error"] = $stmt->errorCode () . " " . $error [2];
            }
        } catch(PDOException $e) {
            // $error = $e->getMessage();
            $result ["result"] = false;
            $result ["error"] = $e->getMessage();
        }
        return $result ; 

    }

    public static function getUserId(){
        return self::$auth['id'];
    }

}