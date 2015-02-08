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
        return $this->createResponse(200, 'join');
    }
}