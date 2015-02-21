<?php

namespace Contentacle\Resources;

/**
 * @uri /login
 */
class Login extends Resource
{
    /**
     * @method get
     * @provides application/hal+yaml
     * @provides application/hal+json
     * @provides text/html
     * @response 200 OK
     */
    function get()
    {
        $response = $this->response(200, 'login');

        $response->addVar('nav', false);
        $response->addLink('self', '/login'.$this->formatExtension());
        $response->addLink('oauth2-token', '/token'.$this->formatExtension());

        return $response;
    }

    /**
     * @method post
     * @accept www-form-urlencoded
     * @response 200 OK
     * @response 302 Found
     */
    function post()
    {
        list($responseCode, $tokenData) = $this->oauth->generateToken();
        
        if ($tokenData) {
            $this->oauth->placeTokenIntoCookie($tokenData);

            $response = $this->response(302);
            $response->location = '/users/'.strtolower($_POST['client_id']);
            return $response;

        } else {
            return $this->get();
        }
    }
}

/**
 * @uri /token
 */
class Token extends Resource
{
    /**
     * @method get
     * @response 302 Found
     */
    function get()
    {
        $response = $this->response(302);
        $response->location = '/login';
        return $response;
    }

    /**
     * @method post
     * @accept www-form-urlencoded
     * @authorization Basic
     * @response 200 OK
     * @response 400 Bad Request
     */
    function createAToken()
    {
        list($responseCode, $tokenData) = $this->oauth->generateToken();
        $response = $this->response($responseCode, 'login');

        $response->addData($tokenData);

        return $response;
    }
}