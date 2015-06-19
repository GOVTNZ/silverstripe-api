<?php

class ApiAuthenticator {

    public static function execute($controller){
        $controller->logAdd('Authenticator executed');
    }

}
