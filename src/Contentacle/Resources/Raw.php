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

        try {
            $document = $repo->document($branch, $path);
            $response = new \Tonic\Response(200, $document['content']);
            $response->contentType = 'text/plain';
            return $response;
        } catch (\Exception $e) {
            throw new \Tonic\NotFoundException;
        }
    }
}