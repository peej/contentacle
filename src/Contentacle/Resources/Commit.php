<?php

namespace Contentacle\Resources;

/**
 * @uri /users/:username/repos/:repo/branches/:branch/commit/:sha
 */
class Commit extends Resource {

    /**
     * @provides text/yaml
     * @provides application/json
     */
    function get($username, $repoName, $branchName, $sha)
    {
    
    }

}