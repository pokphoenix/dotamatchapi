<?php
namespace App\service ;
use API\Database;
use PDO;
use PDOException;
class SheetService {
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
   
    public static function checkVersionSheetData($active,$parsedBody){
        $result = ['result'=>true,'error'=>''];
        $con = Database::getInstance();
        $code = $parsedBody['code'] ;
        $sql = "SELECT * FROM master_data 
                WHERE code=:code ORDER BY ID ASC" ;
        $stmt = $con->dbh->prepare($sql);
        $stmt->bindParam(':code',$code,PDO::PARAM_INT,10);
        try {
            if($stmt->execute()){
                $query = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if(empty($query)){
                    $result ["result"] = false;
                    $result ["error"] = 'not found data';
                }else{
                    if ($query[0]['code']==$code && $query[0]['active']==$active  ){
                        $result['response']['code'] = $code;
                        return $result;
                    }
                    $index = 0;
                    foreach ($query as $key => $q) {
                        $h[$index]['sheet_name'] = $q['sheetname'];
                        $value = ($q['value']) ;
                        $h[$index]['sheet_value'] = (is_null($value)) ? [] : $value ;
                        $index++;
                    }
                    $result['response']['code'] = $query[0]['code'];
                    $result['response']['last_update'] = $query[0]['lastupdate'];
                    $result['response']['sheet'] = $h ;
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

    public static function getSheet($active,$parsedBody) {
        $result = ['result'=>true,'error'=>''];
        $con = Database::getInstance();
        $sql = "SELECT * 
        FROM master_data WHERE active=:active ORDER BY ID ASC" ;
        $stmt = $con->dbh->prepare($sql);
        $stmt->bindParam(':active',$active,PDO::PARAM_INT,1);
        try {
            if($stmt->execute()){
                $query = $stmt->fetchAll(PDO::FETCH_ASSOC);
                if(empty($query)){
                    $result ["result"] = false;
                    $result ["error"] = 'not found data';
                }else{
                    $index = 0;
                    foreach ($query as $key => $q) {
                        $h[$index]['sheet_name'] = $q['sheetname'];
                        $value = ($q['value']) ;
                        $h[$index]['sheet_value'] = (is_null($value)) ? [] : $value ;
                        $index++;
                    }
                    $result['response']['code'] = $query[0]['code'];
                    $result['response']['last_update'] = $query[0]['lastupdate'];
                    $result['response']['sheet'] = $h ;
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
    public static function setPublic($parsedBody) {
        $result = ['result'=>true,'error'=>''];
        $con = Database::getInstance();
        $sql = "SELECT code FROM master_data WHERE active=1 limit 1" ;
        $stmt = $con->dbh->prepare($sql);
        if($stmt->execute()){
            $query = $stmt->fetchAll(PDO::FETCH_COLUMN);
            $code = is_null($query[0]) ? 0 : $query[0] ;
        }
        $result['code'] = $code ;
        $sql = "UPDATE master_data SET active=1 WHERE active = 0" ;
        $stmt = $con->dbh->prepare($sql);
        try {
            if($stmt->execute()){
                if(!empty($code)){
                    $sql = "UPDATE master_data SET active=0 WHERE code = :code" ;
                    $stmt = $con->dbh->prepare($sql);
                    $stmt->bindParam(':code',$code,PDO::PARAM_INT,10);
                    if($stmt->execute()){
                        $result['response'] = "success" ;
                    }else{
                        $error = $stmt->errorInfo ();
                        $result ["result"] = false;
                        $result ["error"] = $stmt->errorCode () . " " . $error [2];
                    }
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


    private static function delUnpublicData(){
        $con = Database::getInstance();
        $sql = "DELETE FROM master_data WHERE active = 0 " ;
        $stmt = $con->dbh->prepare($sql);
        try {
            if($stmt->execute()){
                $result ["result"] = true;
                $result ["error"] = '';
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

    public static function setInitialSheet($excel){
        $con = Database::getInstance();
        foreach ($excel as $key => $ex) {
            $sql = "CALL  SHEET_INITIAL_DATA(
            :key,:value)" ;
            $stmt = $con->dbh->prepare($sql);
            $stmt->bindParam(':key',$ex['key'],PDO::PARAM_STR,255);
            $stmt->bindParam(':value',$ex['value'],PDO::PARAM_STR,255);
      
            try {
                if($stmt->execute()){
                    $result ["result"] = true;
                    $result ["error"] = '';
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
        }
    }

    public static function setSheet($excel){
        $con = Database::getInstance();
        $del = self::delUnpublicData();
        $dataCode = time();
        foreach ($excel as $key => $ex) {
            $sheetName = isset($ex["sheetName"]) ? $ex["sheetName"] : '' ;
            $sheetValue = isset($ex["sheetValue"]) ? json_encode($ex["sheetValue"]) : null ;

            if (!isset($ex["sheetValue"])){
              $excel[$key]["sheetValue"] = [] ;
              // continue;
            }
         
            $sql = "INSERT INTO master_data (sheetname,value,active,lastupdate,code) VALUES (
            :sheetName,:value,0,now(),:code)" ;
            $stmt = $con->dbh->prepare($sql);
            $stmt->bindParam(':sheetName',$sheetName,PDO::PARAM_STR,255);
            $stmt->bindParam(':value',$sheetValue);
            $stmt->bindParam(':code',$dataCode,PDO::PARAM_INT,10);
            try {
                if($stmt->execute()){
                    $result ["result"] = true;
                    $result ["error"] = '';
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
        }
        $result["response"] = $excel;
        return $result ;
    }

}
?>