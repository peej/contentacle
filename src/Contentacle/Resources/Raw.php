<?php

namespace Contentacle\Resources;

/**
 * @uri /users/:username/repos/:repo/branches/:branch/raw/:path
 */
class Raw extends Resource {

    function get($username, $repoName, $branch, $path)
    {
        $repoRepo = $this->container['repo_repository'];

        $path = $this->fixPath($path, $username, $repoName, $branch, 'raw');

        $repo = $repoRepo->getRepo($username, $repoName);
        $repo->loadDocument($branch, $path);

        if ($repo->document) {
            $response = new \Tonic\Response(200, $repo->document['content']);
            $response->contentType = 'text/plain';
            return $response;
        } else {
            throw new \Tonic\NotFoundException;
        }
    }
}