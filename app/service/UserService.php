<?php
namespace App\service ;
use API\Database;
use App\Helper\UtilityService;
use App\middleware\AuthMiddleware;
use PDO;
use PDOException;
class UserService {
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
    public static function user() {
        $result = ['result'=>true,'error'=>''];
        
        $userId =  AuthMiddleware::getUserId();
        $con = Database::getInstance();


        $userData = self::userData($userId);
        if (!$userData['result']){
            return $userData;
        }
        $result['response']['balance'] = $userData['response']['balance'];
        $result['response']['profile'] = $userData['response']['profile'];
        $result['response']['use_item_slot'] = $userData['response']['use_item_slot'];
       
        $hero  =  self::userHeroList($userId);
        if (!$hero['result']){
            return $hero;
        }
        $result['response']['hero'] = $hero['response']['hero'];

        $rune  =  self::userRune($userId);
        if (!$rune['result']){
            return $rune;
        }
        $result['response']['rune'] = $rune['response']['rune']; 

        $inventory  =  self::userInventory($userId);
        if (!$inventory['result']){
            return $inventory;
        }
        $result['response']['inventory'] = $inventory['response']['inventory'];
        return $result;
    }

    public static function userData($userId) {
        $result = ['result'=>true,'error'=>''];
        $con = Database::getInstance();

        $sql = "CALL USER_GET_DATA(:user_id)" ;
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
                $result ["error"] = "not found this user";
                return $result;
            }

            $d = $query;
            $balance['money'] = $d['money'];
            $balance['gem'] = $d['gem'];
            $balance['heart'] = $d['heart'];

            $profile['name'] = $d['name'];
            $profile['level'] = $d['level'];
            $profile['exp'] = $d['exp'];
            $profile['current_hero_id'] = $d['current_hero_id'];

            $item[0] = $d['item_slot_1'];
            $item[1] = $d['item_slot_2'];
            $item[2] = $d['item_slot_3'];

            $result['response']['balance'] = $balance;
            $result['response']['profile'] = $profile;
            $result['response']['use_item_slot'] = $item;
            
        } catch(PDOException $e) {
            // $error = $e->getMessage();
            $result ["result"] = false;
            $result ["error"] = $e->getMessage();
        }

       
        return $result;
    }
    public static function userHeroList($userId) {
        $result = ['result'=>true,'error'=>''];
        $con = Database::getInstance();
        $sql = "CALL HERO_GET_ALL(:user_id)" ;
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
            if(empty($query)){
                $result['response']['hero'] = [];
                return $result;
            }
            $index = 0 ;
            $lastHero = $query[0]['hero_id'] ;
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
            $result['response']['hero'] = $hero; 
        } catch(PDOException $e) {
            // $error = $e->getMessage();
            $result ["result"] = false;
            $result ["error"] = $e->getMessage();
        }
        return $result;
    }

    public static function userRune($userId) {
        $result = ['result'=>true,'error'=>''];
        $con = Database::getInstance();
        $sql = "SELECT * FROM user_runes WHERE user_id=:user_id" ;
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
            if(empty($query)){
                $result['response']['rune']=[];
                return $result;
            }

            foreach ($query as $key => $q) {
                for ($i=0 ; $i<16;$i++){
                    $atk[$i] = $q['red_'.($i+1)];
                }
                for ($i=0 ; $i<16;$i++){
                    $def[$i] = $q['green_'.($i+1)];
                }
                for ($i=0 ; $i<16;$i++){
                    $mag[$i] = $q['blue_'.($i+1)];
                }
                $rune[$key]['id'] = $q['id'];
                $rune[$key]['name'] = $q['name'];
                $rune[$key]['red'] =  $atk;
                $rune[$key]['green'] =  $def;
                $rune[$key]['blue'] =  $mag;
            }
            $result['response']['rune'] = $rune;
        } catch(PDOException $e) {
            // $error = $e->getMessage();
            $result ["result"] = false;
            $result ["error"] = $e->getMessage();
        }

       
        return $result;
    }

    public static function userInventory($userId) {
        $result = ['result'=>true,'error'=>''];
        $con = Database::getInstance();
        $sql = "SELECT product_id,product_type,amount FROM user_inventorys WHERE user_id=:user_id" ;
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
            $inventory['item']=[];
            $inventory['rune']=[];
           
            foreach ($query as $key => $q) {
                $rune = []; $item = [];
                if ($q['product_type']==1){
                    $rune['product_id'] = $q['product_id'];
                    $rune['amount']  = $q['amount'];
                    $inventory['rune'][] = $rune ;
                }elseif ($q['product_type']==0){
                    $item['product_id'] = $q['product_id'];
                    $item['amount']  = $q['amount'];
                    $inventory['item'][] = $item ;
                }

            }

            $result['response']['inventory'] = $inventory;
        } catch(PDOException $e) {
            // $error = $e->getMessage();
            $result ["result"] = false;
            $result ["error"] = $e->getMessage();
        }
        return $result;
    }

     public static function setItem($parsedBody) {
        $result = ['result'=>true,'error'=>''];
        $userId =  AuthMiddleware::getUserId();
        $con = Database::getInstance();
        $itemSlot = 3 ;
        $item = $parsedBody['item'];
   
        $sqlItem= '';
      

        $replaceNumber = 0;
       

        $cntItem = count($item) ;
        if ($cntItem < $itemSlot ){
            $replaceNumber = $itemSlot-$cntItem;
            for ($i=0;$i<$replaceNumber;$i++){
                $item[] = 0 ;
            }
        }
      
        for ($i=1;$i<=$itemSlot;$i++){
            $sqlItem .= ",:item_".$i ; 
        
        }
        $sqlItem = substr( $sqlItem , 1);
    
        $sql = "CALL ITEM_USE_SET(:user_id,$sqlItem,@is_success,@msg)";
        $stmt = $con->dbh->prepare($sql);
        $stmt->bindParam(':user_id',$userId,PDO::PARAM_INT);
        for ($i=0;$i<$itemSlot;$i++){
            $stmt->bindParam(':item_'.($i+1),$item[$i],PDO::PARAM_STR);
        }
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
            for ($i=0;$i<$itemSlot;$i++){
                $result['response']['item_slot'][$i] = $item[$i] ;
            }
           
        } catch(PDOException $e) {
            $error = $e->getMessage();
            $result ["result"] = false;
            $result ["error"] = $e->getMessage();
        }
       
        return $result;
    }

    public static function userGetSettingItem() {
        $result = ['result'=>true,'error'=>''];
        $userId =  AuthMiddleware::getUserId();
        $con = Database::getInstance();
        $sql = "SELECT item_slot_1,item_slot_2,item_slot_3 FROM users WHERE id=:user_id";
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
            $result['response']['item_slot'][0] = $query['item_slot_1']; 
            $result['response']['item_slot'][1] = $query['item_slot_2']; 
            $result['response']['item_slot'][2] = $query['item_slot_3']; 
        } catch(PDOException $e) {
            $error = $e->getMessage();
            $result ["result"] = false;
            $result ["error"] = $e->getMessage();
        }
       
        return $result;
    }
    
     public static function editName($parsedBody) {
        $result = ['result'=>true,'error'=>''];
        $userId =  AuthMiddleware::getUserId();
        $con = Database::getInstance();
    
        $name = $parsedBody['name'];    

        $sql = "CALL USER_EDIT_NAME(:user_id,:name,@is_success,@msg)";
        $stmt = $con->dbh->prepare($sql);
        $stmt->bindParam(':user_id',$userId,PDO::PARAM_INT);
        $stmt->bindParam(':name',$name,PDO::PARAM_STR,255);
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
            $result['response']['status'] = 'success';
        } catch(PDOException $e) {
            $error = $e->getMessage();
            $result ["result"] = false;
            $result ["error"] = $e->getMessage();
        }
       
        return $result;
    }
}
?>