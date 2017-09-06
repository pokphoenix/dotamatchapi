<?php
namespace App\Helper ;

use API\Database;
use PDO;
class UtilityService {
    private static $instance;
    public static function getInstance() {
        if (!isset(self::$instance))
        {
            $object = __CLASS__;
            self::$instance = new $object;
        }
        return self::$instance;
    }

    public static function deviceDetection() {
        $device = 'web' ;
       
        if( stristr($_SERVER['HTTP_USER_AGENT'],'ipad') ) {
            $device = "ipad";
        } else if( stristr($_SERVER['HTTP_USER_AGENT'],'iphone') || strstr($_SERVER['HTTP_USER_AGENT'],'iphone') ) {
            $device = "iphone";
        } else if( stristr($_SERVER['HTTP_USER_AGENT'],'blackberry') ) {
            $device = "blackberry";
        } else if( stristr($_SERVER['HTTP_USER_AGENT'],'android') ) {
            $device = "android";
        }
        return $device;   
    }

    public static function get_client_ip() {
        $ipaddress = '';
        if (getenv('HTTP_CLIENT_IP'))
            $ipaddress = getenv('HTTP_CLIENT_IP');
        else if(getenv('HTTP_X_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_X_FORWARDED_FOR');
        else if(getenv('HTTP_X_FORWARDED'))
            $ipaddress = getenv('HTTP_X_FORWARDED');
        else if(getenv('HTTP_FORWARDED_FOR'))
            $ipaddress = getenv('HTTP_FORWARDED_FOR');
        else if(getenv('HTTP_FORWARDED'))
           $ipaddress = getenv('HTTP_FORWARDED');
        else if(getenv('REMOTE_ADDR'))
            $ipaddress = getenv('REMOTE_ADDR');
        else
            $ipaddress = 'UNKNOWN';
    return $ipaddress;
}
}
?>