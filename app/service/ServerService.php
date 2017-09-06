<?php
namespace App\service ;

use API\Database;
use PDO;
use PDOException;
class ServerService {
    
    function __construct() {
    }

    public static function getServerTime() {
        $result = ['result'=>true,'error'=>''];
        $con = Database::getInstance();
        $sql = 'select UTC_TIMESTAMP() as date';
        $stmt = $con->dbh->prepare($sql);
        try {
            if($stmt->execute()){
                $query = $stmt->fetchAll(PDO::FETCH_COLUMN);
                if(empty($query)){
                    $result ["result"] = false;
                    $result ["error"] = 'cannot connect server';
                }else{
                    $result['data'] = strtotime($query[0]);
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