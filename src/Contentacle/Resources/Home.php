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
        return new \Tonic\Response(200, array(
            'users' => '/users'
        ));
    }

}