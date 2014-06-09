<?php

namespace Contentacle\Resources;

/**
 * @uri /
 * @uri /home
 */
class Home extends Resource {

    /**
     * @provides text/yaml
     * @provides application/json
     */
    function get()
    {
        $response = new \Contentacle\Responses\Hal();

        $response->addLink('self', '/'.$this->formatExtension());
        $response->addLink('users', '/users'.$this->formatExtension());

        return $response;
    }

}