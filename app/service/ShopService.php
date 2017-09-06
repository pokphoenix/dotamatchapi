<?php
namespace App\service ;
use API\Database;
use App\Helper\UtilityService;
use App\middleware\AuthMiddleware;
use PDO;
use PDOException;
class ShopService {
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
    public static function buyItem($parsedBody) {
        $result = ['result'=>true,'error'=>''];
        $productType = 0 ;
        $userId =  AuthMiddleware::getUserId();
        $con = Database::getInstance();

        $productId = $parsedBody['product_id'];
        $amount = $parsedBody['amount'];
        $price = $parsedBody['price'];
        $balanceType = $parsedBody['balance_type'];
        $sql = "CALL BUY_PRODUCT(:user_id,:product_id,:product_type,:amount,:balance_type,:price,@is_success,@msg)";
        $stmt = $con->dbh->prepare($sql);
        $stmt->bindParam(':user_id',$userId,PDO::PARAM_INT);
        $stmt->bindParam(':product_id',$productId,PDO::PARAM_STR,255);
        $stmt->bindParam(':product_type',$productType,PDO::PARAM_INT);
        $stmt->bindParam(':amount',$amount,PDO::PARAM_INT);
        $stmt->bindParam(':balance_type',$balanceType,PDO::PARAM_STR,20);
        $stmt->bindParam(':price',$price,PDO::PARAM_INT);
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

            $userData = UserService::userData($userId);
            if (!$userData['result']){
                return $userData;
            }
            $result['response']['balance'] = $userData['response']['balance'];

            $inventory  =  UserService::userInventory($userId);
            if (!$inventory['result']){
                return $inventory;
            }
            $result['response']['inventory'] = $inventory['response']['inventory']; 

        } catch(PDOException $e) {
            $error = $e->getMessage();
            $result ["result"] = false;
            $result ["error"] = $e->getMessage();
        }
       
        return $result;
    }

    public static function sellItem($parsedBody) {
        $result = ['result'=>true,'error'=>''];
        $productType = 0 ;
        $userId =  AuthMiddleware::getUserId();
        $con = Database::getInstance();
        $productId = $parsedBody['product_id'];
        $amount = $parsedBody['amount'];
        $price = $parsedBody['price'];
        $sql = "CALL ITEM_SELL(:user_id,:product_id,:product_type,:amount,:price,@is_success,@msg)";
        $stmt = $con->dbh->prepare($sql);
        $stmt->bindParam(':user_id',$userId,PDO::PARAM_INT);
        $stmt->bindParam(':product_id',$productId,PDO::PARAM_STR,255);
        $stmt->bindParam(':product_type',$productType,PDO::PARAM_INT);
        $stmt->bindParam(':amount',$amount,PDO::PARAM_INT);
        $stmt->bindParam(':price',$price,PDO::PARAM_INT);
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

            $userData = UserService::userData($userId);
            if (!$userData['result']){
                return $userData;
            }
            $result['response']['balance'] = $userData['response']['balance'];

            $inventory  =  UserService::userInventory($userId);
            if (!$inventory['result']){
                return $inventory;
            }
            $result['response']['inventory'] = $inventory['response']['inventory']; 

        } catch(PDOException $e) {
            $error = $e->getMessage();
            $result ["result"] = false;
            $result ["error"] = $e->getMessage();
        }
       
        return $result;
    }
}
?>