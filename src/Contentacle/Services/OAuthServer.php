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

        return $this->verifyResourceRequest(\OAuth2\Request::createFromGlobals());
    }

    function getUsername()
    {
        if (isset($_COOKIE['access_token'])) {
            $_GET['access_token'] = $_COOKIE['access_token'];
        }

        $tokenData = $this->getAccessTokenData(\OAuth2\Request::createFromGlobals());

        return $tokenData['client_id'];
    }

    function placeTokenIntoCookie($tokenData)
    {
        setcookie('access_token', $tokenData['access_token'], time() + $tokenData['expires_in']);
    }

    function removeTokenFromCookie()
    {
        setcookie('access_token', '', time() - 3600);
    }
}