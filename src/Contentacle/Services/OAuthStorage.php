<?php

namespace Contentacle\Services;

class OAuthStorage implements
    \OAuth2\Storage\AccessTokenInterface,
    \OAuth2\Storage\ClientCredentialsInterface,
    \OAuth2\Storage\AuthorizationCodeInterface
{
    private $tokenDir;
    private $authCodeDir;
    private $userRepo;

    function __construct($tokenDir, $authCodeDir, $userRepo)
    {
        $this->tokenDir = $tokenDir;
        $this->authCodeDir = $authCodeDir;
        $this->userRepo = $userRepo;
    }

    public function getAuthorizationCode($code)
    {
        $filename = $this->authCodeDir.'/'.$code.'.json';
        if (file_exists($filename)) {
            return json_decode(file_get_contents($filename));
        }
        return null;
    }

    public function setAuthorizationCode($code, $clientId, $userId, $redirectUri, $expires, $scope = null)
    {
        $filename = $this->authCodeDir.'/'.$code.'.json';
        $data = array(
            'expires' => $expires,
            'client_id' => $clientId,
            'user_id' => $userId,
            'redirect_uri' => $redirectUri,
            'scope' => $scope
        );

        file_put_contents($filename, json_encode($data));
    }

    public function expireAuthorizationCode($code)
    {
        unlink($this->authCodeDir.'/'.$code.'.json');
    }

    public function getAccessToken($oauthToken)
    {
        $filename = $this->tokenDir.'/'.$oauthToken.'.json';
        if (file_exists($filename)) {
            return json_decode(file_get_contents($filename), true);
        }
        return null;
    }

    public function setAccessToken($oauthToken, $clientId, $userId, $expires, $scope = null)
    {
        $filename = $this->tokenDir.'/'.$oauthToken.'.json';
        $data = array(
            'expires' => $expires,
            'client_id' => $clientId,
            'user_id' => $userId,
            'scope' => $scope,
            'id_token' => $oauthToken
        );

        file_put_contents($filename, json_encode($data));
    }

    public function getClientDetails($clientId)
    {
        return array(
            'redirect_uri' => 'http://localhost:8080/'
        );
    }

    public function getClientScope($clientId)
    {
        return '';
    }

    public function checkRestrictedGrantType($clientId, $grantType)
    {
        return true;
    }

    public function checkClientCredentials($username, $password = null)
    {
        try {
            $user = $this->userRepo->getUser($username);
            if ($password === null) {
                return true;
            }
            return $user->verifyPassword($password);
        } catch (\Contentacle\Exceptions\UserException $e) {}
        return false;
    }

    public function isPublicClient($username)
    {
        return false;
    }


}