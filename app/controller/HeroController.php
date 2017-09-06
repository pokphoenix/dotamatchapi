<?php
namespace App\controller ;
use App\service\HeroService ;
class HeroController {
    private $_service ;
    public function __construct() {
       $this->_service = HeroService::GetInstance();
    }
    public function buyHero ($request, $response, $args)
    {
        global $apiResult ;
        $parsedBody = $request->getParsedBody();
        $data = $this->_service->buyHero($parsedBody);
        $apiResult = array_merge($apiResult,$data);
    }  
    public function setHeroEquipment ($request, $response, $args)
    {
        global $apiResult ;
        $parsedBody = $request->getParsedBody();
        $data = $this->_service->setHeroEquipment($parsedBody);
        $apiResult = array_merge($apiResult,$data);
    } 
    public function getHeroEquipmentTopten ($request, $response, $args)
    {
        global $apiResult ;
        $parsedBody = $request->getParsedBody();
        if(!isset($parsedBody['hero_id'])){
            $apiResult['result'] = false;
            $apiResult['error'] = 'กรุณาระบุ hero_id';
        }

        $data = $this->_service->getHeroEquipmentTopten($parsedBody);
        $apiResult = array_merge($apiResult,$data);
    }  
    public function setHeroEquipmentTopten ($request, $response, $args)
    {
        global $apiResult ;
        $parsedBody = $request->getParsedBody();
        if(!isset($parsedBody['hero_id'])){
            $apiResult['result'] = false;
            $apiResult['error'] = 'กรุณาระบุ hero_id';
        }
        if(!isset($parsedBody['topten_id'])){
            $apiResult['result'] = false;
            $apiResult['error'] = 'กรุณาระบุ topten_id';
        }


        $data = $this->_service->setHeroEquipmentTopten($parsedBody);
        $apiResult = array_merge($apiResult,$data);
    } 
    public function setHeroSkill ($request, $response, $args)
    {
        global $apiResult ;
        $parsedBody = $request->getParsedBody();
        $data = $this->_service->setHeroSkill($parsedBody);
        $apiResult = array_merge($apiResult,$data);
    } 
    public function selectCurrentHero ($request, $response, $args)
    {
        global $apiResult ;
        $parsedBody = $request->getParsedBody();
        $data = $this->_service->selectCurrentHero($parsedBody);
        $apiResult = array_merge($apiResult,$data);
    } 
    public function getHero ($request, $response, $args)
    {
        global $apiResult ;
        $parsedBody = $request->getParsedBody();
        $data = $this->_service->getHero($parsedBody);
        $apiResult = array_merge($apiResult,$data);
    } 
   
}