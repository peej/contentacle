<?php

namespace Contentacle\Resources;

/**
 * @uri /logout
 */
class Logout extends Resource
{
    /**
     * @method post
     * @response 302 Found
     */
    function post()
    {
        $this->oauth->removeTokenFromCookie();

        $response = $this->response(302);
        $response->location = '/';
        return $response;
    }
}