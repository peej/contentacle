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
        return $this->response(200, 'join');
    }
}