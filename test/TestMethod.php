<?php

use API\App ;
use Slim\Http\Environment;
use Slim\Http\Headers;
use Slim\Http\Request;
use Slim\Http\RequestBody;
use Slim\Http\Response;
use Slim\Http\Uri;

class TestMethod extends PHPUnit_Framework_TestCase 
{
    protected $app ;
    private  $token ;  

    public function request($method,$url,$data=null){
        $app = (new App())->get() ; 
        if ( strtolower($method)=='GET'){
            $env = Environment::mock([
            'REQUEST_METHOD' => 'GET',
            'REQUEST_URI'    => $url,
            ]);
            $req = Request::createFromEnvironment($env);
            $app->getContainer()['request'] = $req;
            $response = $app->run(true);
        }else{
             $env = Environment::mock([
                'REQUEST_METHOD' => $method ,
                'REQUEST_URI'    => $url,
                'CONTENT_TYPE'   => 'application/x-www-form-urlencoded',
            ]);
            $req = Request::createFromEnvironment($env)->withParsedBody($data);
            $app->getContainer()['request'] = $req;
            $response = $app->run(true);
        }
        return $response ;

    }
    
}

