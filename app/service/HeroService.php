<?php
namespace App\service ;
use API\Database;
use App\Helper\UtilityService;
use App\middleware\AuthMiddleware;
use PDO;
use PDOException;
class HeroService {
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
    public static function buyHero($parsedBody) {
        $result = ['result'=>true,'error'=>''];
        $userId =  AuthMiddleware::getUserId();
        $con = Database::getInstance();

        $heroId = $parsedBody['hero_id'];
        $day = $parsedBody['rent_day'];
        $balanceType = $parsedBody['balance_type'];
        $price = $parsedBody['price'];

        $sql = "CALL BUY_HERO(:user_id,:hero_id,:rent_day,:balance_type,:price,@is_success,@msg)";
        $stmt = $con->dbh->prepare($sql);
        $stmt->bindParam(':user_id',$userId,PDO::PARAM_INT);
        $stmt->bindParam(':hero_id',$heroId,PDO::PARAM_STR,10);
        $stmt->bindParam(':rent_day',$day,PDO::PARAM_INT);
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

            $hero  =  UserService::userHeroList($userId);
            if (!$hero['result']){
                return $hero;
            }
            $result['response']['hero'] = $hero['response']['hero']; 

        } catch(PDOException $e) {
            $error = $e->getMessage();
            $result ["result"] = false;
            $result ["error"] = $e->getMessage();
        }
       
        return $result;
    }

    public static function getHero($parsedBody) {
        $result = ['result'=>true,'error'=>''];
        $productType = 1 ;
        $userId =  AuthMiddleware::getUserId();
        $con = Database::getInstance();

        $hero  =  UserService::userHeroList($userId);
        if (!$hero['result']){
            return $hero;
        }
        $result['response']['hero'] = $hero['response']['hero']; 
       
        return $result;
    }

    
    

    public static function setHeroEquipment($parsedBody) {
        $result = ['result'=>true,'error'=>''];
        $userId =  AuthMiddleware::getUserId();
        $con = Database::getInstance();

        $equipmentSlot = 6;

        $heroId = $parsedBody['hero_id'];
        $equipment = $parsedBody['equipment'];
        
        $SqlEquipment = '';
      
        $cntEquipment = count($equipment) ;

        if ($cntEquipment < $equipmentSlot ){
            $replaceNumber = $equipmentSlot-$cntEquipment;
            for ($i=0;$i<$replaceNumber;$i++){
                $equipment[] = '0' ;
            }
        }

        for ($i=1;$i<=$equipmentSlot;$i++){
            $SqlEquipment .= ",:eqm_slot_".$i ; 
        }
        $SqlEquipment = substr( $SqlEquipment , 1);

        $sql = "CALL HERO_SET_EQUIPMENT(:user_id,:hero_id,$SqlEquipment)";
        $stmt = $con->dbh->prepare($sql);
        $stmt->bindParam(':user_id',$userId,PDO::PARAM_INT);
        $stmt->bindParam(':hero_id',$heroId,PDO::PARAM_STR,50);
        for ($i=0;$i<$equipmentSlot;$i++){
            $stmt->bindParam(':eqm_slot_'.($i+1),$equipment[$i],PDO::PARAM_STR,11);
        }
        try {
            if(!$stmt->execute()){
                $error = $stmt->errorInfo ();
                $result ["result"] = false;
                $result ["error"] = $stmt->errorCode () . " " . $error [2];
                return $result;
            }
            $query = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if(empty($query)){
                $result['response'] = [];
                return $result;
            }
            $hero = self::queryHeroData($query) ;
            $result['response'] = $hero; 
        } catch(PDOException $e) {
            $error = $e->getMessage();
            $result ["result"] = false;
            $result ["error"] = $e->getMessage();
        }
       
        return $result;
    }

    public static function setHeroSkill($parsedBody) {
        $result = ['result'=>true,'error'=>''];
        $userId =  AuthMiddleware::getUserId();
        $con = Database::getInstance();

     
        $heroId = $parsedBody['hero_id'];
        $skills = $parsedBody['skill_plan'];
    
        foreach ($skills as $key => $skill) {
            self::insertHeroSkill($userId,$heroId,$key,$skill);
        }
    
        $sql = " CALL HERO_GET_BY_ID(:user_id,:hero_id)";
        $stmt = $con->dbh->prepare($sql);
        $stmt->bindParam(':user_id',$userId,PDO::PARAM_INT);
        $stmt->bindParam(':hero_id',$heroId,PDO::PARAM_STR,11);
        try {
            if(!$stmt->execute()){
                $error = $stmt->errorInfo ();
                $result ["result"] = false;
                $result ["error"] = $stmt->errorCode () . " " . $error [2];
                return $result;
            }
            $query = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if(empty($query)){
                $result['response']= [];
                return $result;
            }
            $hero = self::queryHeroData($query) ;
            $result['response'] = $hero; 
        } catch(PDOException $e) {
            $error = $e->getMessage();
            $result ["result"] = false;
            $result ["error"] = $e->getMessage();
        }
       
        return $result;
    }

    private static function queryHeroData($query){
        $lastHero = $query[0]['hero_id'] ;
        $index = 0;
        foreach ($query as $key => $q) {
            if ($lastHero!=$q['hero_id']){
                $lastHero = $q['hero_id'];
                $index++;
            }
           
            $hero[$index]['hero_id']    = $q['hero_id'];
            $hero[$index]['eqm_slot_1'] = $q['eqm_slot_1'];
            $hero[$index]['eqm_slot_2'] = $q['eqm_slot_2'];
            $hero[$index]['eqm_slot_3'] = $q['eqm_slot_3'];
            $hero[$index]['eqm_slot_4'] = $q['eqm_slot_4'];
            $hero[$index]['eqm_slot_5'] = $q['eqm_slot_5'];
            $hero[$index]['eqm_slot_6'] = $q['eqm_slot_6'];

            if (isset($q['skill_index'])){
                $hero[$index]['skill_plan'][] = $q['skill_id'] ;
            }else{
                $hero[$index]['skill_plan'] = [];
            }
        }
        return $hero[0];
    }

    private static function insertHeroSkill($userId,$heroId,$index,$skillId) {
        $result = ['result'=>true,'error'=>''];
        $userId =  AuthMiddleware::getUserId();
        $con = Database::getInstance();
        $sql = "CALL HERO_SET_SKILL(:user_id,:hero_id,:index,:skill_id)";
        $stmt = $con->dbh->prepare($sql);
        $stmt->bindParam(':user_id',$userId,PDO::PARAM_INT);
        $stmt->bindParam(':hero_id',$heroId,PDO::PARAM_STR,11);
        $stmt->bindParam(':index',$index,PDO::PARAM_INT);
        $stmt->bindParam(':skill_id',$skillId,PDO::PARAM_STR,50);
        try {
            if(!$stmt->execute()){
                $error = $stmt->errorInfo ();
                $result ["result"] = false;
                $result ["error"] = $stmt->errorCode () . " " . $error [2];
                return $result;
            }
        } catch(PDOException $e) {
            $error = $e->getMessage();
            $result ["result"] = false;
            $result ["error"] = $e->getMessage();
        }
       
        return $result;
    }

    public static function selectCurrentHero($parsedBody) {
        $result = ['result'=>true,'error'=>''];
        $userId =  AuthMiddleware::getUserId();
        $con = Database::getInstance();

        $heroId = $parsedBody['hero_id'];

        $sql = "CALL HERO_SELECT_CURRENT(:user_id,:hero_id,@is_success,@msg)";
        $stmt = $con->dbh->prepare($sql);
        $stmt->bindParam(':user_id',$userId,PDO::PARAM_INT);
        $stmt->bindParam(':hero_id',$heroId,PDO::PARAM_STR,11);
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
            $result['response'] = 'success';
        } catch(PDOException $e) {
            $error = $e->getMessage();
            $result ["result"] = false;
            $result ["error"] = $e->getMessage();
        }
       
        return $result;
    }
     public static function getHeroEquipmentTopten($parsedBody) {
        $result = ['result'=>true,'error'=>''];
        $con = Database::getInstance();
        $heroId = $parsedBody['hero_id'];
       
        $sql = "CALL HERO_EQUIPMENT_TOPTEN(:hero_id)";
        $stmt = $con->dbh->prepare($sql);
       
        $stmt->bindParam(':hero_id',$heroId,PDO::PARAM_STR,11);
        
        try {
            if(!$stmt->execute()){
                $error = $stmt->errorInfo ();
                $result ["result"] = false;
                $result ["error"] = $stmt->errorCode () . " " . $error [2];
                return $result;
            }
            $query = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if(empty($query)){
                $result['response']['list'] = [];
                return $result;
            }
            $result['response']['list'] = $query; 
        } catch(PDOException $e) {
            $error = $e->getMessage();
            $result ["result"] = false;
            $result ["error"] = $e->getMessage();
        }
       
        return $result;
    }
    public static function setHeroEquipmentTopten($parsedBody) {
        $result = ['result'=>true,'error'=>''];
        $userId =  AuthMiddleware::getUserId();
        $con = Database::getInstance();
        $heroId = $parsedBody['hero_id'];
        $toptenId = $parsedBody['topten_id'];
       
        $sql = "CALL HERO_SET_EQUIPMENT_FROM_TOPTEN(:user_id,:hero_id,:topten_id)";
        $stmt = $con->dbh->prepare($sql);
        $stmt->bindParam(':user_id',$userId,PDO::PARAM_INT);
        $stmt->bindParam(':hero_id',$heroId,PDO::PARAM_STR,11);
        $stmt->bindParam(':topten_id',$toptenId,PDO::PARAM_INT);
        
        try {
            if(!$stmt->execute()){
                $error = $stmt->errorInfo ();
                $result ["result"] = false;
                $result ["error"] = $stmt->errorCode () . " " . $error [2];
                return $result;
            }
            $query = $stmt->fetchAll(PDO::FETCH_ASSOC);
            if(empty($query)){
                $result['response']['list'] = [];
                return $result;
            }
            $hero = self::queryHeroData($query) ;
            $result['response'] = $hero; 
        } catch(PDOException $e) {
            $error = $e->getMessage();
            $result ["result"] = false;
            $result ["error"] = $e->getMessage();
        }
       
        return $result;
    }
}
?>