<?php
namespace App\controller ;
use App\service\RuneService ;
class RuneController {
    private $_service ;
    public function __construct() {
       $this->_service = RuneService::GetInstance();
    }
    
    public function buyRune ($request, $response, $args)
    {
        global $apiResult ;
        $parsedBody = $request->getParsedBody();
        $data = $this->_service->buyRune($parsedBody);
        $apiResult = array_merge($apiResult,$data);
    }  
    public function buyRunePage ($request, $response, $args)
    {
        global $apiResult ;
        $parsedBody = $request->getParsedBody();
        $data = $this->_service->buyRunePage($parsedBody);
        $apiResult = array_merge($apiResult,$data);
    } 
    public function getRuneInventory ($request, $response, $args)
    {
        global $apiResult ;
        $parsedBody = $request->getParsedBody();
        $data = $this->_service->getRuneInventory($parsedBody);
        $apiResult = array_merge($apiResult,$data);
    }  
    public function setRune ($request, $response, $args)
    {
        global $apiResult ;
        $parsedBody = $request->getParsedBody();
        $data = $this->_service->setRune($parsedBody);
        $apiResult = array_merge($apiResult,$data);
    } 
    public function getRune ($request, $response, $args)
    {
        global $apiResult ;
        $parsedBody = $request->getParsedBody();
        $data = $this->_service->getRune($parsedBody);
        $apiResult = array_merge($apiResult,$data);
    }  
    public function sellRune ($request, $response, $args)
    {
        global $apiResult ;
        $parsedBody = $request->getParsedBody();
        $data = $this->_service->sellRune($parsedBody);
        $apiResult = array_merge($apiResult,$data);
    } 
   
}