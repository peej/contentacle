<?php

namespace Contentacle\Services;

class OAuthServer extends \OAuth2\Server
{
    private $request;

    private function request()
    {
        if (!$this->request) {
            $this->request = \OAuth2\Request::createFromGlobals();
        }
        return $this->request;
    }

    function generateToken()
    {
        $tokenResponse = parent::handleTokenRequest($this->request());
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

        return $this->verifyResourceRequest($this->request());
    }

    function getUsername()
    {
        if (isset($_COOKIE['access_token'])) {
            $_GET['access_token'] = $_COOKIE['access_token'];
        }

        $tokenData = $this->getAccessTokenData($this->request());

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