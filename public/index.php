<?php
require '../vendor/autoload.php';
Header('Content-Type: application/json; charset=UTF-8');
$apiResult['result'] =true ;
$apiResult['error'] ='' ;
$apiResult['response_time'] = 0 ;
set_time_limit(0);
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Run app
$app = (new API\App())->get();
$app->run();

// require_once('../config/setting.php');
// $app = new \Slim\App(['settings' =>$config]);

// $app->post('/', function ($request,$response) {
//     $dbhost = "localhost";
//     $dbname = "ferretki_dota";
//     $dbuser = "ferretki_dota";
//     $dbpass = "dota1234";
//     $dbh = new PDO("mysql:host=$dbhost;dbname=$dbname", $dbuser, $dbpass);  
//     $dbh->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
// 	$guid = 'a380415e15af698956dab0b8c44cde8860430e6c';
//     $token = 'af4e7e940b73a6f27a033358f340a80f33ea3ecc';
//     $tokenExpire =  date('Y-m-d' , time()+86400) ;
//     $initMoney = 100000 ;
//     $initGem = 100 ;
//     $name = "GUEST".time();
  
//     $sql = "INSERT INTO users (name,guid,token,token_expire,money,gem,created_at,updated_at)
//             VALUES (:name,:guid,:token,:token_expire,:money,:gem,now(),now())" ;
//     $stmt = $dbh->prepare($sql);
//     $stmt->bindParam(':name',$name,PDO::PARAM_STR,40);
//     $stmt->bindParam(':guid',$guid,PDO::PARAM_STR,40);
//     $stmt->bindParam(':token',$token,PDO::PARAM_STR,40);
//     $stmt->bindParam(':token_expire',$tokenExpire,PDO::PARAM_STR,10);
//     $stmt->bindParam(':money',$initMoney,PDO::PARAM_INT,6);
//     $stmt->bindParam(':gem',$initGem,PDO::PARAM_INT,3);
//     try {
//         if($stmt->execute()){
//             $result['data']['guid'] = $guid;
//             $result['data']['token'] = $token;
//             $result['data']['name'] = $name;
//             $result['data']['money'] = $initMoney;
//             $result['data']['gem'] = $initGem;
//         }else{
//             $error = $stmt->errorInfo ();
//             $result ["result"] = false;
//             $result ["error"] = $stmt->errorCode () . " " . $error [2];
//         }
//     } catch(PDOException $e) {
//         $error = $e->getMessage();
//         $result ["result"] = false;
//         $result ["error"] = $e->getMessage();
//     }
//     var_dump($result);die;
//     return  $response->withHeader('Content-Type', 'application/json')->write(json_encode($result, JSON_NUMERIC_CHECK));
// });


