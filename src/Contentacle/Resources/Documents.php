<?php

namespace Contentacle\Resources;

/**
 * @uri /users/:username/repos/:repo/branches/:branch/documents
 * @uri /users/:username/repos/:repo/branches/:branch/documents/:path
 */
class Documents extends Resource {

    /**
     * @provides text/yaml
     * @provides application/json
     */
    function get($username, $repoName, $branch, $path = null)
    {
        $repoRepo = $this->container['repo_repository'];

        $path = $this->fixPath($path, $username, $repoName, $branch, 'documents');
        
        $repo = $repoRepo->getRepo($username, $repoName);
        $repo->loadDocuments($branch, $path);
        
        if ($repo->documents) {
            return new \Tonic\Response(200, $repo->documents);
        } elseif ($repo->document) {
            return new \Tonic\Response(200, $repo->document);
        } else {
            throw new \Tonic\NotFoundException;
        }
    }
}
