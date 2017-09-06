<?php 
abstract class Environment
{
    const Localhost = 0;
    const Server = 1;
}

if (file_exists("environment.txt")){
    $myfile = fopen("environment.txt", "r") or die("Unable to open file!");
    $file = fread($myfile,filesize("environment.txt"));
    $environment = constant('Environment::'. $file) ;
    fclose($myfile);
}else{
    $environment = Environment::Server;
}
switch ($environment) {
    case Environment::Localhost:
        $config['db']['host'] = "localhost";
        $config['db']['user'] = "root";
        $config['db']['pass'] = "";
        $config['db']['dbname'] = "ferretki_dota";
        break;
    case Environment::Server:
        $config['db']['host'] = "localhost";
        $config['db']['user'] = "ferretki_dota";
        $config['db']['pass'] = "dota1234";
        $config['db']['dbname'] = "ferretki_dota";
        break;

}

