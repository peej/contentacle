<?php

namespace Contentacle\Resources;

/**
 * @uri /
 * @uri /home
 */
class Home extends Resource {

    /**
     * @provides application/hal+yaml
     * @provides application/hal+json
     */
    function get()
    {
        $response = $this->createHalResponse();

        $response->addLink('self', '/'.$this->formatExtension());
        $response->addLink('cont:users', '/users'.$this->formatExtension());

        return $response;
    }

    /**
     * @method get
     * @provides text/html
     */
    function getHtml()
    {
        return $this->createHtmlResponse('home.html');
    }

}