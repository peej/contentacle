<?php

namespace Contentacle\Resources;

/**
 * @uri /users/:username/repos/:repo/branches/:branch/history/:path
 */
class History extends Resource {

    /**
     * @provides text/yaml
     * @provides application/json
     */
    function get($username, $repoName, $branch, $path = null)
    {
        $repoRepo = $this->container['repo_repository'];

        $path = $this->fixPath($path, $username, $repoName, $branch, 'history');

        $repo = $repoRepo->getRepo($username, $repoName);
        try {
            $history = $repo->history($branch, $path);
            return new \Tonic\Response(200, $history);
        } catch (\Exception $e) {
            throw new \Tonic\NotFoundException;
        }
    }
}