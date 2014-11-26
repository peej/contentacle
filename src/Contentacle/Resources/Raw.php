<?php

namespace Contentacle\Resources;

/**
 * @uri /users/:username/repos/:repo/branches/:branch/raw/(.+)$
 */
class Raw extends Resource
{
    /**
     * Get the raw contents of a document.
     *
     * @method get
     * @response 200 OK
     */
    function get($username, $repoName, $branch, $path)
    {
        $repoRepo = $this->getRepoRepository();

        $path = $this->fixPath($path, $username, $repoName, $branch, 'raw');

        $repo = $repoRepo->getRepo($username, $repoName);

        try {
            $document = $repo->document($branch, $path);
            if ($document) {
                $response = new \Tonic\Response(200, $document['content']);
                $response->contentType = 'text/plain';
                return $response;
            }
        
        } catch (\Contentacle\Exceptions\RepoException $e) {}
        throw new \Tonic\NotFoundException;
    }
}