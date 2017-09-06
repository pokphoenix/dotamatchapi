<?php
namespace App\service ;
use App\Helper\UtilityService;
use API\Database;
use PDO;
use PDOException;
class LoginService {
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
    private static function generateGuid($device,$ipaddress){
        return sha1(HASH.$ipaddress.$device);
    }
    private static function generateToken($guid,$id=null){
        return isset($id) ? sha1(time().$guid.$id) : sha1(time().$guid) ; 
    } 

    private static function refreshToken($data){
        $token = self::generateToken($data['guid'],$data['token']);
        $tokenExpire = date('Y-m-d' , time()+TOKEN_EXPIRE);
        $con = Database::getInstance();
        $sql = "UPDATE users 
                SET token = :token 
                , token_expire = :token_expire
                WHERE id=:user_id" ;
        $stmt = $con->dbh->prepare($sql);
        $stmt->bindParam(':user_id',$data['id'],PDO::PARAM_STR,40);
        $stmt->bindParam(':token',$token,PDO::PARAM_STR,40);
        $stmt->bindParam(':token_expire',$tokenExpire,PDO::PARAM_STR,40);
        try {
            if($stmt->execute()){
                $result ["result"] = true;
                $result ["error"] = '';
                $result ["token"]= $token ;
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

    public static function loginGuid($data,$is_login=false) {
        $result = ['result'=>true,'error'=>''];
        $con = Database::getInstance();
        $guid = $data['guid'] ;
        $sql = "CALL USER_LOGIN (:guid,:is_login);" ;
        $stmt = $con->dbh->prepare($sql);
        $stmt->bindParam(':guid',$guid,PDO::PARAM_STR,40);
        $stmt->bindParam(':is_login',$is_login,PDO::PARAM_BOOL);
        try {
            if($stmt->execute()){
                $query = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if(empty($query)){
                    $result ["result"] = false;
                    $result ["error"] = 'cannot connect server';
                }else{
                    $d = $query[0];
                    $token = $d['token'];
                    if($d['refresh_token']){
                        $reftoken = self::refreshToken($d);
                        if(!$reftoken['result']){
                            return $reftoken ;
                        }
                        $token = $reftoken['token'];
                    }

                    $result['response']['guid'] = $d['guid'] ;
                    $result['response']['token'] = $token ;
                    $result['response']['name'] = $d['name'];
                    $result['response']['money'] = $d['money'];
                    $result['response']['gem'] = $d['gem'];
                    $result['response']['heart'] = $d['heart'];
                    $result['response']['facebook_id'] = $d['facebook_id'];
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

       
        return $result;
    }

    public static function registerGuid() {
        $result = ['result'=>true,'error'=>''];
        $util = UtilityService::getInstance();
        $device = $util->deviceDetection();
        $ipaddress = $util->get_client_ip();
        $guid = self::generateGuid($device,$ipaddress);
        $token = self::generateToken($guid);
        $tokenExpire =  date('Y-m-d' , time()+TOKEN_EXPIRE) ;
        $initMoney = INIT_MONEY ;
        $initGem = INIT_GEM ;
        $initHeart = INIT_HEART ;
        $name = "GUEST".time();
        $data['guid'] = $guid;
        $check =  self::loginGuid($data);
        if($check['result']){
            return $check ;
        }
        $con = Database::getInstance();
        $sql = "CALL USER_REGISTER_GUID(:name,:guid,:token,:token_expire)";
        $stmt = $con->dbh->prepare($sql);
        $stmt->bindParam(':name',$name,PDO::PARAM_STR,40);
        $stmt->bindParam(':guid',$guid,PDO::PARAM_STR,40);
        $stmt->bindParam(':token',$token,PDO::PARAM_STR,40);
        $stmt->bindParam(':token_expire',$tokenExpire,PDO::PARAM_STR,10);
        try {
            if($stmt->execute()){
                $query = $stmt->fetch(PDO::FETCH_ASSOC);
                $result['response']['guid'] = $query['guid'];
                $result['response']['token'] = $query['token'];
                $result['response']['name'] = $query['name'];
                $result['response']['money'] = $query['money'];
                $result['response']['gem'] = $query['gem'];
                $result['response']['heart'] = $query['heart'];
                $result['response']['facebook_id'] = $query['facebook_id'];
            }else{
                $error = $stmt->errorInfo ();
                $result ["result"] = false;
                $result ["error"] = $stmt->errorCode () . " " . $error [2];
            }
        } catch(PDOException $e) {
            $error = $e->getMessage();
            $result ["result"] = false;
            $result ["error"] = $e->getMessage();
        }
       
        return $result;
    }

    public static function registerFB($data) {
        $result = ['result'=>true,'error'=>''];
        $facebookId = $data['facebook_id'];
        $name = $data['name'];
        $email = $data['email'];
  
        $util = UtilityService::getInstance();
        $device = $util->deviceDetection();
        $guid = self::generateGuid($device,$facebookId);
        $token = self::generateToken($guid);
        $tokenExpire =  date('Y-m-d' , time()+TOKEN_EXPIRE) ;
        $initMoney = INIT_MONEY ;
        $initGem = INIT_GEM ;
        $initHeart = INIT_HEART ;
        $data['guid'] = $guid;
        $check =  self::checkRepeatUser($data);
        if($check['result']){
            return $check ;
        }
        $con = Database::getInstance();
        // $sql = "INSERT INTO users (name,email,facebook_id,guid,token,token_expire,money,gem,heart,created_at,updated_at)
        //         VALUES (:name,:email,:facebook_id,:guid,:token,:token_expire,:money,:gem,:heart,now(),now())" ;
        $sql = "CALL USER_REGISTER_FB(:name,:email,:facebook_id,:guid,:token,:token_expire)";
        $stmt = $con->dbh->prepare($sql);
        $stmt->bindParam(':name',$name,PDO::PARAM_STR,40);
        $stmt->bindParam(':email',$email,PDO::PARAM_STR,255);
        $stmt->bindParam(':facebook_id',$facebookId,PDO::PARAM_STR,255);
        $stmt->bindParam(':guid',$guid,PDO::PARAM_STR,40);
        $stmt->bindParam(':token',$token,PDO::PARAM_STR,40);
        $stmt->bindParam(':token_expire',$tokenExpire,PDO::PARAM_STR,10);
        try {
            if($stmt->execute()){
                $query = $stmt->fetch(PDO::FETCH_ASSOC);
                $result['response']['guid'] = $query['guid'];
                $result['response']['token'] = $query['token'];
                $result['response']['name'] = $query['name'];
                $result['response']['money'] = $query['money'];
                $result['response']['gem'] = $query['gem'];
                $result['response']['heart'] = $query['heart'];
                $result['response']['facebook_id'] = $query['facebook_id'];
            }else{
                $error = $stmt->errorInfo ();
                $result ["result"] = false;
                $result ["error"] = $stmt->errorCode () . " " . $error [2];
            }
        } catch(PDOException $e) {
            $error = $e->getMessage();
            $result ["result"] = false;
            $result ["error"] = $e->getMessage();
        }
       
        return $result;
    }
     public static function checkRepeatUser($data) {
        $result = ['result'=>true,'error'=>''];
        $con = Database::getInstance();
        $email = $data['email'] ;
        $sql = "SELECT name,guid,token,money,gem FROM users WHERE email=:email" ;
        $stmt = $con->dbh->prepare($sql);
        $stmt->bindParam(':email',$email,PDO::PARAM_STR,40);
        try {
            if($stmt->execute()){
                $query = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if(empty($query)){
                    $result ["result"] = false;
                    $result ["error"] = 'cannot connect server';
                }else{
                    $d = $query[0];
                    $result['data']['guid'] = $d['guid'];
                    $result['data']['token'] = $d['token'];
                    $result['data']['name'] = $d['name'];
                    $result['data']['money'] = $d['money'];
                    $result['data']['gem'] = $d['gem'];
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

       
        return $result;
    }
}
?>