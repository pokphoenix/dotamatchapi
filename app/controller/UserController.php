<?php
namespace App\controller ;
use App\service\UserService ;

class UserController {
    private $_user ;
    public function __construct() {
       $this->_user = UserService::GetInstance();
    }
    
    public function userData ($request, $response, $args)
    {
        global $apiResult ;
        $parsedBody = $request->getParsedBody();
        $data = $this->_user->user();
        $apiResult = array_merge($apiResult,$data);
    }  
    public function setItem ($request, $response, $args)
    {
        global $apiResult ;
        $parsedBody = $request->getParsedBody();
        $data = $this->_user->setItem($parsedBody);
        $apiResult = array_merge($apiResult,$data);
    }  
    public function editName ($request, $response, $args)
    {
        global $apiResult ;
        $parsedBody = $request->getParsedBody();
        if(!isset($parsedBody['name'])){
            $apiResult['result'] = false;
            $apiResult['error'] = 'กรุณาระบุ name';
            return $apiResult;
        }

        $data = $this->_user->editName($parsedBody);
        $apiResult = array_merge($apiResult,$data);
    } 
   
}