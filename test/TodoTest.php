<?php
use API\App;
use Slim\Http\Environment;
use Slim\Http\Request;

require_once('TestMethod.php');

class TodoTest extends PHPUnit_Framework_TestCase
{
    protected $app;
    public function setUp()
    {
        $this->app = (new App())->get();
    }
    public function testLogin() {

    	$response = TestMethod::request('GET','/sheet/get/unpublic');

        // $env = Environment::mock([
        //     'REQUEST_METHOD' => 'GET',
        //     'REQUEST_URI'    => '/auth/list',
        //     ]);
        // $req = Request::createFromEnvironment($env);
        // $this->app->getContainer()['request'] = $req;
        // $response = $this->app->run(true);
        $this->assertSame($response->getStatusCode(), 200);
        // $this->assertSame((string)$response->getBody(), "Hello, Todo");
    }
    // public function testTodoGet() {
    //     // $env = Environment::mock([
    //     //     'REQUEST_METHOD' => 'GET',
    //     //     'REQUEST_URI'    => '/server/time',
    //     //     ]);
    //     // $req = Request::createFromEnvironment($env);
    //     // $this->app->getContainer()['request'] = $req;
    //     // $response = $this->app->run(true);
    //     $response = TestMethod::request('GET','/server/time');
    //     $this->assertSame($response->getStatusCode(), 200);
    //     // $this->assertSame((string)$response->getBody(), "Hello, Todo");
    // }
    // public function testTodoGetAll() {
    //     $env = Environment::mock([
    //         'REQUEST_METHOD' => 'GET',
    //         'REQUEST_URI'    => '/todo',
    //         ]);
    //     $req = Request::createFromEnvironment($env);
    //     $this->app->getContainer()['request'] = $req;
    //     $response = $this->app->run(true);
    //     $this->assertSame($response->getStatusCode(), 200);
    //     $result = json_decode($response->getBody(), true);
    //     $this->assertSame($result["message"], "Hello, Todo");
    // } 
    // public function testTodoPost() {
    //     $id = 1;
    //     $env = Environment::mock([
    //         'REQUEST_METHOD' => 'POST',
    //         'REQUEST_URI'    => '/todo/'.$id,
    //         'CONTENT_TYPE'   => 'application/x-www-form-urlencoded',
    //     ]);
    //     $req = Request::createFromEnvironment($env)->withParsedBody([]);
    //     $this->app->getContainer()['request'] = $req;
    //     $response = $this->app->run(true);
    //     $this->assertSame($response->getStatusCode(), 200);
    //     $result = json_decode($response->getBody(), true);
    //     $this->assertSame($result["message"], "Todo ".$id." updated successfully");
    // } 
    // public function testTodoDelete() {
    //     $id = 1;
    //     $env = Environment::mock([
    //         'REQUEST_METHOD' => 'DELETE',
    //         'REQUEST_URI'    => '/todo/'.$id,
    //         'CONTENT_TYPE'   => 'application/x-www-form-urlencoded',
    //     ]);
    //     $req = Request::createFromEnvironment($env)->withParsedBody([]);
    //     $this->app->getContainer()['request'] = $req;
    //     $response = $this->app->run(true);
    //     $this->assertSame($response->getStatusCode(), 200);
    //     $result = json_decode($response->getBody(), true);
    //     $this->assertSame($result["message"], "Todo ".$id." deleted successfully");
    // } 
}