<?php
namespace App\controller ;
use App\service\ShopService ;

class ShopController {
    private $_service ;
    public function __construct() {
       $this->_service = ShopService::GetInstance();
    }
    
    public function sell ($request, $response, $args)
    {
        global $apiResult ;
        $parsedBody = $request->getParsedBody();
        $data = $this->_service->sellItem($parsedBody);
        $apiResult = array_merge($apiResult,$data);
    } 

    public function buy ($request, $response, $args)
    {
        global $apiResult ;
        $parsedBody = $request->getParsedBody();
        $data = $this->_service->buyItem($parsedBody);
        $apiResult = array_merge($apiResult,$data);
    } 
   
}