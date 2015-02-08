<?php

namespace Contentacle\Resources;

/**
 * @uri /
 * @uri /home
 */
class Home extends Resource
{
    /**
     * @method get
     * @provides application/hal+yaml
     * @provides application/hal+json
     * @provides text/html
     */
    function get()
    {
        $response = $this->createResponse(200, 'home');

        $response->addVar('nav', false);
        $response->addLink('self', '/'.$this->formatExtension());
        $response->addLink('cont:users', '/users'.$this->formatExtension());

        return $response;
    }
}