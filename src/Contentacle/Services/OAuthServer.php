<?php

namespace Contentacle\Services;

class OAuthServer extends \OAuth2\Server
{
    function generateToken()
    {
        $tokenResponse = parent::handleTokenRequest(\OAuth2\Request::createFromGlobals());
        return array(
            $tokenResponse->getStatusCode(),
            $tokenResponse->getParameters()
        );
    }

    function verifyToken()
    {
        if (isset($_COOKIE['access_token'])) {
            $_GET['access_token'] = $_COOKIE['access_token'];
        }

        return parent::verifyResourceRequest(\OAuth2\Request::createFromGlobals());
    }

    function placeTokenIntoCookie($tokenData)
    {
        setcookie('access_token', $tokenData['access_token'], time() + $tokenData['expires_in']);
    }
}