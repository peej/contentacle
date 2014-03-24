<?php

namespace Contentacle\Resources;

/**
 * @uri /users/:username/repos/:repo/branches/:branch/commit/:sha
 */
class Commit extends Resource {

    /**
     * @method get
     */
    function get($username, $repoName, $branchName, $sha)
    {
    
    }

}