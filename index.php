<?php
require 'vendor/autoload.php';
$apiResult['result'] =true ;
$apiResult['error'] ='' ;
$apiResult['response_time'] = 0 ;
$apiResult['response'] = null ;
set_time_limit(0);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Run app
// $app = (new API\App())->get();
// $app->run();
$root = str_replace('src', '', realpath(__DIR__ )) ;

use App\controller\HeroController ;
use App\controller\LoginController ;
use App\controller\PlayController ;
use App\controller\RuneController ;
use App\controller\SheetController ;
use App\controller\ShopController ;
use App\controller\UserController ;


use App\middleware\AuthMiddleware;
use App\middleware\JsonMiddleware;
use App\service\ServerService;

require_once(__DIR__.'/config/setting.php');


$config['determineRouteBeforeAppMiddleware'] = true;
$config['displayErrorDetails'] = true;
$config['addContentLengthHeader'] = false;

$app = new \Slim\App(["settings" => $config]);
// $app->get('/{token}', function ($req, $res) {
//     return $res->write('Center');
// })->add(new AuthMiddleware)->add(new JsonMiddleware);
// $container = $app->getContainer();
// $app->add(new JsonMiddleware);   

$Auth = new AuthMiddleware ;
$middleware = new JsonMiddleware;

$app->get('/server/time', function ( $request, $response, $args) {
	global $apiResult ;
    $return  = ServerService::getServerTime();
    $apiResult['response_time'] = $return['data'];
    unset($apiResult['response']);
    $makeResponse = json_encode($apiResult, JSON_NUMERIC_CHECK);
    return $response->withHeader('Content-Type', 'application/json')->write($makeResponse);
});


$app->group('/auth', function () {
    // require_once($root.'app/controller/LoginController.php');
    $con = LoginController::class;
    $this->post('/login_guid', $con.':loginGuid');
    $this->post('/login_fb', $con.':loginFB');
})->add($middleware);

$app->group('/user', function () {
    $con = UserController::class;
    $this->get('/data/{token}',  $con.':userData');
    $this->post('/setitem/{token}',  $con.':setItem');
    $this->post('/editname/{token}',  $con.':editName');
})->add($Auth)->add($middleware);

$app->group('/shop', function () {
    $con = ShopController::class;
    $this->post('/buy/{token}',  $con.':buy');
    $this->post('/sell/{token}',  $con.':sell');

})->add($Auth)->add($middleware);

$app->group('/rune', function () {
    $con = RuneController::class;
    $this->post('/setting/{token}',  $con.':setRune');
    $this->post('/buy/{token}',  $con.':buyRune');
    $this->post('/sell/{token}',  $con.':sellRune');
    $this->get('/buy/page/{token}',  $con.':buyRunePage');
    $this->get('/inventory/{token}',  $con.':getRuneInventory');
    $this->get('/get/{token}',  $con.':getRune');

})->add($Auth)->add($middleware);

$app->group('/hero', function () {
    $con = HeroController::class;
    $this->post('/equipment/{token}',  $con.':setHeroEquipment');
    $this->post('/skill/{token}',  $con.':setHeroSkill');
    $this->post('/current/{token}',  $con.':selectCurrentHero');
    $this->post('/buy/{token}',  $con.':buyHero');
    $this->get('/get/{token}',  $con.':getHero');
    $this->post('/equipment/topten/{token}',  $con.':getHeroEquipmentTopten');
    $this->post('/set/topten/{token}',  $con.':setHeroEquipmentTopten');
})->add($Auth)->add($middleware);

$app->group('/play', function () {
    $con = PlayController::class;
    $this->get('/start/{token}',  $con.':startGame');
    $this->post('/end/{token}',  $con.':endGame');
    $this->post('/useitem/{token}',  $con.':useItem');
})->add($Auth)->add($middleware);


$app->group('/sheet', function () {
    $con = SheetController::class ;
    $this->get('/set',$con.':setData');
    $this->post('/get/{public}',$con.':getData');
    $this->post('/set_public',$con.':setPublic');
})->add($middleware);



$app->run();
?>


