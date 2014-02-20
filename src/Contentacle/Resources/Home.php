<?php

namespace Contentacle\Resources;

/**
 * @uri /
 */
class Home extends Resource {

    /**
     * @method get
     * @template home.html
     */
    function get()
    {
        return [200];
    }

}