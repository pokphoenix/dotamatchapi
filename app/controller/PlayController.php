<?php
namespace App\controller ;
use App\service\PlayService ;
class PlayController {
    private $_service ;
    public function __construct() {
       $this->_service = PlayService::GetInstance();
    }
    public function startGame ($request, $response, $args)
    {
        global $apiResult ;
        $parsedBody = $request->getParsedBody();
        $data = $this->_service->startGame($parsedBody);
        $apiResult = array_merge($apiResult,$data);
    }  
    public function endGame ($request, $response, $args)
    {
        global $apiResult ;
        $parsedBody = $request->getParsedBody();

        if (!isset($parsedBody['exp'])){
            $apiResult['result'] = false ;
            $apiResult['error'] = 'please insert exp' ;
            return $apiResult;
        } 
        if (!isset($parsedBody['money'])){
            $apiResult['result'] = false ;
            $apiResult['error'] = 'please insert money' ;
            return $apiResult;
        } 
        if (!isset($parsedBody['hero_user_id'])){
            $apiResult['result'] = false ;
            $apiResult['error'] = 'please insert hero_user_id' ;
            return $apiResult;
        } 
        if (!isset($parsedBody['hero_enemy_id'])){
            $apiResult['result'] = false ;
            $apiResult['error'] = 'please insert hero_enemy_id' ;
            return $apiResult;
        } 
        if (!isset($parsedBody['match_status'])){
            $apiResult['result'] = false ;
            $apiResult['error'] = 'please insert match_status' ;
            return $apiResult;
        } 
        if ( $parsedBody['match_status'] != 1 AND $parsedBody['match_status'] != 0 ){
            $apiResult['result'] = false ;
            $apiResult['error'] = 'match_status is 0 or 1 only' ;
            return $apiResult;
        }

        $heroEnemyId = isset($parsedBody['hero_enemy_id']) ? $parsedBody['hero_enemy_id'] : '' ; 
        $matchStatus = isset($parsedBody['match_status']) ? $parsedBody['match_status'] : '' ;

        $data = $this->_service->endGame($parsedBody);
        $apiResult = array_merge($apiResult,$data);
    }  
    public function useItem ($request, $response, $args)
    {
        global $apiResult ;
        $parsedBody = $request->getParsedBody();
        $data = $this->_service->useItem($parsedBody);
        $apiResult = array_merge($apiResult,$data);
    } 
    
   
}