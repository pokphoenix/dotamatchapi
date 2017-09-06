<?php
namespace App\middleware ;

use App\service\ServerService;
class JsonMiddleware
{
    
    public function __invoke($request, $response, $next)
    {
        global $apiResult ;
        $return  = ServerService::getServerTime();
        $apiResult['response_time'] = $return['data'];
        $response = $next($request, $response);
        $makeResponse = $this->makeResponse ();
        return $response->withHeader('Content-Type', 'application/json;charset=utf-8')->write($makeResponse);
    }

    private function makeResponse(){
        global $apiResult;
        
        if (isset($apiResult['forcedata'])){
            unset($apiResult['forcedata']);
            $returnData = json_encode($apiResult);
        }else{
            $returnData = json_encode($apiResult, JSON_NUMERIC_CHECK) ;
        }
        return $returnData; 
    }
}