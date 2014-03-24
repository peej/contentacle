<?php

namespace Contentacle\Resources;

/**
 * @uri /
 * @uri /home
 */
class Home extends Resource {

    function get()
    {
        return new \Tonic\Response(200, array(
            'users' => '/users'
        ));
    }

}