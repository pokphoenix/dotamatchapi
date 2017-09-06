<?php
namespace App\service ;
use API\Database;
use App\Helper\UtilityService;
use App\middleware\AuthMiddleware;
use PDO;
use PDOException;
class RuneService {
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
    public static function buyRune($parsedBody) {
        $result = ['result'=>true,'error'=>''];
        $productType = 1 ;
        $userId =  AuthMiddleware::getUserId();
        $con = Database::getInstance();

        $productId = $parsedBody['product_id'];
        $amount = $parsedBody['amount'];
        $price = $parsedBody['price'];
        $balanceType = 'money';
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

            $rune  =  self::getRuneInventory($userId);
            if (!$rune['result']){
                return $rune;
            }
            $result['response']['rune'] = $rune['response']['rune']; 

        } catch(PDOException $e) {
            $error = $e->getMessage();
            $result ["result"] = false;
            $result ["error"] = $e->getMessage();
        }
       
        return $result;
    }

    public static function buyRunePage($parsedBody) {
        $result = ['result'=>true,'error'=>''];
        $productType = 1 ;
        $userId =  AuthMiddleware::getUserId();
        $con = Database::getInstance();

        $productId = $parsedBody['product_id'];
        $amount = $parsedBody['amount'];
        $price = $parsedBody['price'];
        $sql = "CALL RUNE_BUY_PAGE(:user_id,@is_success,@msg)";
        $stmt = $con->dbh->prepare($sql);
        $stmt->bindParam(':user_id',$userId,PDO::PARAM_INT);
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
            $rune  =  self::getRune($userId);
            if (!$rune['result']){
                return $rune;
            }
            $result['response']['rune'] = $rune['response']['rune']; 

        } catch(PDOException $e) {
            $error = $e->getMessage();
            $result ["result"] = false;
            $result ["error"] = $e->getMessage();
        }
       
        return $result;
    }

    public static function getRuneInventory($parsedBody) {
        $result = ['result'=>true,'error'=>''];
        $productType = 1 ;
        $userId =  AuthMiddleware::getUserId();
        $con = Database::getInstance();

        $productId = $parsedBody['product_id'];
        $amount = $parsedBody['amount'];
        $price = $parsedBody['price'];
        $sql = "CALL RUNE_GET(:user_id)";
        $stmt = $con->dbh->prepare($sql);
        $stmt->bindParam(':user_id',$userId,PDO::PARAM_INT);
        try {
            if(!$stmt->execute()){
                 $error = $stmt->errorInfo ();
                $result ["result"] = false;
                $result ["error"] = $stmt->errorCode () . " " . $error [2];
                return $result;
            }
            $query = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $result['response']['rune'] = $query; 
        } catch(PDOException $e) {
            $error = $e->getMessage();
            $result ["result"] = false;
            $result ["error"] = $e->getMessage();
        }
       
        return $result;
    }
    public static function getRune($parsedBody) {
        $result = ['result'=>true,'error'=>''];
        $productType = 1 ;
        $userId =  AuthMiddleware::getUserId();
        $con = Database::getInstance();

        $rune  =  UserService::userRune($userId);
        if (!$rune['result']){
            return $rune;
        }
        $result['response']['rune'] = $rune['response']['rune']; 
       
        return $result;
    }

    public static function setRune($parsedBody) {
        $result = ['result'=>true,'error'=>''];
        $userId =  AuthMiddleware::getUserId();
        $con = Database::getInstance();
        $runeSlot = 16 ;
        $name = $parsedBody['name'];
        $red = $parsedBody['red'];
        $green = $parsedBody['green'];
        $blue = $parsedBody['blue'];

        $SqlRed = '';
        $SqlGreen = '';
        $SqlBlue = '';

        $replaceNumber = 0;
       

        $cntRed = count($red) ;
        if ($cntRed < $runeSlot ){
            $replaceNumber = $runeSlot-$cntRed;
            for ($i=0;$i<$replaceNumber;$i++){
                $red[] = 0 ;
            }
        }
        $cntGreen = count($green) ;
        if ($cntGreen < $runeSlot ){
            $replaceNumber = $runeSlot-$cntGreen;
            for ($i=0;$i<$replaceNumber;$i++){
                $green[] = 0 ;
            }
        }
        $cntBlue = count($blue) ;
        if ($cntBlue < $runeSlot ){
            $replaceNumber = $runeSlot-$cntBlue;
            for ($i=0;$i<$replaceNumber;$i++){
                $blue[] = 0 ;
            }
        }

        for ($i=1;$i<=$runeSlot;$i++){
            $SqlRed .= ",:red_".$i ; 
            $SqlGreen .= ",:green_".$i ; 
            $SqlBlue .= ",:blue_".$i ; 
        }
        $SqlRed = substr( $SqlRed , 1);
        $SqlGreen = substr( $SqlGreen , 1);
        $SqlBlue = substr( $SqlBlue , 1);

        $sql = "CALL RUNE_SET(:user_id,:rune_id,:rune_name,$SqlRed,$SqlGreen,$SqlBlue)";
        $stmt = $con->dbh->prepare($sql);
        $stmt->bindParam(':user_id',$userId,PDO::PARAM_INT);
        $stmt->bindParam(':rune_id',$runeId,PDO::PARAM_INT);
        $stmt->bindParam(':rune_name',$name,PDO::PARAM_INT);
        for ($i=0;$i<$runeSlot;$i++){
            $stmt->bindParam(':red_'.($i+1),$red[$i],PDO::PARAM_INT);
            $stmt->bindParam(':green_'.($i+1),$green[$i],PDO::PARAM_INT);
            $stmt->bindParam(':blue_'.($i+1),$blue[$i],PDO::PARAM_INT);
        }
        try {
            if(!$stmt->execute()){
                 $error = $stmt->errorInfo ();
                $result ["result"] = false;
                $result ["error"] = $stmt->errorCode () . " " . $error [2];
                return $result;
            }
            $rune  =  UserService::userRune($userId);
            if (!$rune['result']){
                return $rune;
            }
            $result['response']['rune'] = $rune['response']['rune']; 
        } catch(PDOException $e) {
            $error = $e->getMessage();
            $result ["result"] = false;
            $result ["error"] = $e->getMessage();
        }
       
        return $result;
    }

    public static function sellRune($parsedBody) {
        $result = ['result'=>true,'error'=>''];
        $productType = 1 ;
        $userId =  AuthMiddleware::getUserId();
        $con = Database::getInstance();
        $productId = $parsedBody['product_id'];
        $amount = $parsedBody['amount'];
        $price = $parsedBody['price'];
        $sql = "CALL RUNE_SELL(:user_id,:product_id,:product_type,:amount,:price,@is_success,@msg)";
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

            $rune  =  self::getRuneInventory($userId);
            if (!$rune['result']){
                return $rune;
            }
            $result['response']['rune'] = $rune['response']['rune']; 

        } catch(PDOException $e) {
            $error = $e->getMessage();
            $result ["result"] = false;
            $result ["error"] = $e->getMessage();
        }
       
        return $result;
    }
}
?>