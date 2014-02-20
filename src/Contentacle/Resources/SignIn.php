<?php

namespace Contentacle\Resources;

/**
 * @uri /signin
 */
class SignIn extends Resource {

    /**
     * @method get
     */
    function get()
    {
        return new \Tonic\Response(302, null, ['Location' => '/peej']);
    }

}