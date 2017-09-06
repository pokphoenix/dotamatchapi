<?php 

$app->group('/server', function () use ($root,$Auth){
    require_once($root.'app/controller/ServerController.php');
    $this->get('/time',function ($request,$response) {
    	// var_dump("test");die;
	    return $response;
	});
	$this->get('/test/{token}','ServerController:test')->add($Auth);
});


$test['test'] = '33';