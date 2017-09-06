<?php
namespace App\service ;
use API\Database;
use App\Helper\UtilityService;
use App\middleware\AuthMiddleware;
use PDO;
use PDOException;
class PlayService {
    private static $instance;

    function __construct() {
    }

    public static function getInstance() {
        if (!isset(self::$instance))
        {
            $object = __CLASS__;
            self::$instance = new $object;
        }
        return self::$instance;
    }
    public static function startGame($parsedBody) {
        $result = ['result'=>true,'error'=>''];
        $userId =  AuthMiddleware::getUserId();
        $con = Database::getInstance();
        $sql = "CALL GAMEPLAY_START(:user_id)";
        $stmt = $con->dbh->prepare($sql);
        $stmt->bindParam(':user_id',$userId,PDO::PARAM_INT);
        try {
            if(!$stmt->execute()){
                 $error = $stmt->errorInfo ();
                $result ["result"] = false;
                $result ["error"] = $stmt->errorCode () . " " . $error [2];
                return $result;
            }
            $query = $stmt->fetch(PDO::FETCH_ASSOC);
            if(empty($query)){
                $result ["result"] = false;
                $result ["error"] = 'cannot find this user';
                return $result;
            }

            $response['heart'] = $query['heart'];
            $response['exp'] = $query['exp'];

            $result['response'] = $response ;
            

        } catch(PDOException $e) {
            $error = $e->getMessage();
            $result ["result"] = false;
            $result ["error"] = $e->getMessage();
        }
       
        return $result;
    }
    public static function endGame($parsedBody) {
        $result = ['result'=>true,'error'=>''];
        $userId =  AuthMiddleware::getUserId();

        $exp = $parsedBody['exp'];
        $money = $parsedBody['money'];
        $heroUserId = $parsedBody['hero_user_id'];
        $heroEnemyId = $parsedBody['hero_enemy_id'];
        $matchStatus = $parsedBody['match_status'];
     
        $con = Database::getInstance();
        $sql = "CALL GAMEPLAY_END(:user_id,:exp,:money,:hero_user_id,:hero_enemy_id,:i_match_status)";
        $stmt = $con->dbh->prepare($sql);
        $stmt->bindParam(':user_id',$userId,PDO::PARAM_INT);
        $stmt->bindParam(':exp',$exp,PDO::PARAM_INT);
        $stmt->bindParam(':money',$money,PDO::PARAM_INT);
        $stmt->bindParam(':hero_user_id',$heroUserId,PDO::PARAM_STR,11);
        $stmt->bindParam(':hero_enemy_id',$heroEnemyId,PDO::PARAM_STR,11);
        $stmt->bindParam(':i_match_status',$matchStatus,PDO::PARAM_INT,1);
        try {
            if(!$stmt->execute()){
                 $error = $stmt->errorInfo ();
                $result ["result"] = false;
                $result ["error"] = $stmt->errorCode () . " " . $error [2];
                return $result;
            }

            $query = $stmt->fetch(PDO::FETCH_ASSOC);
            if(empty($query)){
                $result ["result"] = false;
                $result ["error"] = 'cannot find this user';
                return $result;
            }
            $result['response'] = $query ;

        } catch(PDOException $e) {
            $error = $e->getMessage();
            $result ["result"] = false;
            $result ["error"] = $e->getMessage();
        }
       
        return $result;
    }

    public static function useItem($parsedBody) {
        $result = ['result'=>true,'error'=>''];
        $userId =  AuthMiddleware::getUserId();

        $productId = $parsedBody['product_id'];

        $con = Database::getInstance();
        $sql = "CALL GAMEPLAY_USE_ITEM(:user_id,:product_id,@is_success,@msg)";
        $stmt = $con->dbh->prepare($sql);
        $stmt->bindParam(':user_id',$userId,PDO::PARAM_INT);
        $stmt->bindParam(':product_id',$productId,PDO::PARAM_INT);
        try {
            if(!$stmt->execute()){
                 $error = $stmt->errorInfo ();
                $result ["result"] = false;
                $result ["error"] = $stmt->errorCode () . " " . $error [2];
                return $result;
            }
            $sql = "SELECT @is_success AS success , @msg AS msg ";
            $stmt = $con->dbh->prepare($sql);
            $stmt->execute();
            $query = $stmt->fetch(PDO::FETCH_ASSOC);
            if ( empty($query['success']) || $query['success']==0 ){
                $result['result'] = false ;
                $result['error'] = $query['msg'] ;
                return $result;
            }
           
            $item  =  self::getItemInventory($userId);
            if (!$item['result']){
                return $item;
            }
            $result['response']['item'] = $item['response']['item']; 

        } catch(PDOException $e) {
            $error = $e->getMessage();
            $result ["result"] = false;
            $result ["error"] = $e->getMessage();
        }
       
        return $result;
    }

    private static function getItemInventory($parsedBody) {
        $result = ['result'=>true,'error'=>''];
        $userId =  AuthMiddleware::getUserId();
        $con = Database::getInstance();
        $sql = "CALL ITEM_GET_BY_TYPE(:user_id,0)";
        $stmt = $con->dbh->prepare($sql);
        $stmt->bindParam(':user_id',$userId,PDO::PARAM_INT);
        $stmt->bindParam(':user_id',$userId,PDO::PARAM_INT);
        try {
            if(!$stmt->execute()){
                 $error = $stmt->errorInfo ();
                $result ["result"] = false;
                $result ["error"] = $stmt->errorCode () . " " . $error [2];
                return $result;
            }
            $query = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $result['response']['item'] = $query; 
        } catch(PDOException $e) {
            $error = $e->getMessage();
            $result ["result"] = false;
            $result ["error"] = $e->getMessage();
        }
       
        return $result;
    }
    
}
?>