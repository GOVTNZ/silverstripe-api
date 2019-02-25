<?php

namespace GovtNZ\SilverStripe\Api;

class ApiAuthenticator
{
    public static function execute($controller)
    {
        $controller->logAdd('Authenticator executed');
    }
}
