<?php

namespace Contentacle\Resources;

/**
 * @uri /join
 */
class Join extends Resource
{
    /**
     * Display the sign up form.
     *
     * @method get
     * @response 200 OK
     * @provides text/html
     */
    function get()
    {
        $response = $this->response(200, 'join');

        $this->configureResponse($response);
        $response->addLink('cont:users', '/users'.$this->formatExtension());

        return $response;
    }
}