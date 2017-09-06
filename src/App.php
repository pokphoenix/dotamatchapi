<?php
namespace API;
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;
use App\middleware\JsonMiddleware ;
use App\middleware\AuthMiddleware ;
use App\controller\SheetController ;
use App\controller\LoginController ;

class App
{
    /**
     * Stores an instance of the Slim application.
     *
     * @var \Slim\App
     */
    private $app;
    public function __construct() {

      

        $root = str_replace('src', '', realpath(__DIR__ )) ;
        require_once(__DIR__.'/../config/setting.php');

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
    

        $app->group('/hello', function () use ($root) {
            $this->get('/world',function () use ($root){
                 echo "hello world" ;
            });
        });


        $app->group('/auth', function () use ($root) {
            // require_once($root.'app/controller/LoginController.php');
            $login = LoginController::class;
            $this->post('/login_guid', $login.':loginGuid');
            $this->post('/login_fb', $login.':loginFB');
            //  $this->post('/login_guid', 'LoginController:loginGuid');
            // $this->post('/login_fb', 'LoginController:loginFB');
        })->add(new JsonMiddleware);

        $app->group('/server', function () use ($root,$Auth){
            // require_once($root.'app/controller/ServerController.php');
            $this->get('/time',function ($request,$response) {
                return $response;
            });
            // $this->get('/test/{token}','ServerController:test')->add($Auth);
        })->add(new JsonMiddleware);
        
        $app->group('/sheet', function () use ($root){
            $sheet = SheetController::class ;
            $this->get('/set',$sheet.':setData');
            $this->get('/get/{public}',$sheet.':getData');
            $this->post('/set_public',$sheet.':setPublic');
        })->add(new JsonMiddleware);
        
        $this->app = $app;
    }
    /**
     * Get an instance of the application.
     *
     * @return \Slim\App
     */
    public function get()
    {
        return $this->app;
    }

}


