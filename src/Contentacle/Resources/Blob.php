<?php

namespace Contentacle\Resources;

/**
 * @uri /users/:username/repos/:repo/branches/:branch/documents/(.+)
 */
class Blob extends Resource {

    /**
     * @method get
     */
    function get($username, $repoName, $branchName, $path)
    {
        
    }

}