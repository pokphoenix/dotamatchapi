<?php
namespace App\service ;
use App\Helper\UtilityService;
use API\Database;
use PDO;
use PDOException;
class AuthService {
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

    public static function loginGuid($token) {
        $result = ['result'=>true,'error'=>''];
        $con = Database::getInstance();
        $guid = $data['guid'] ;
        $sql = "SELECT id,name,guid,token,money,gem,facebook_id
        ,CASE WHEN token_expire < now() THEN true ELSE false END as refresh_token  
        FROM users WHERE guid=:guid" ;
        $stmt = $con->dbh->prepare($sql);
        $stmt->bindParam(':guid',$guid,PDO::PARAM_STR,40);
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

    
}
?>