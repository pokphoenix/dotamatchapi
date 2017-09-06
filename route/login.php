<?php 
$app->group('/auth', function () use ($root) {
    require_once($root.'app/controller/LoginController.php');
    $this->post('/login_guid','LoginController:loginGuid');
    $this->post('/login_fb','LoginController:loginFB');
    // $this->post('/register','AuthController:register');
    // $this->get('/list','AuthController:index');
    // $this->group('/settings', 'Auth:register');
});