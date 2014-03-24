<?php

namespace Contentacle\Resources;

/**
 * @uri /users/:username/repos
 */
class Repos extends Resource {

    /**
     * @method get
     * @json
     */
    function get($username)
    {
        return [200, ['test']];
    }

}