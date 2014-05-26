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
        try {
            $documents = $repo->documents($branch, $path);
            return new \Tonic\Response(200, $documents);
        } catch (\Exception $e) {
            try {
                $document = $repo->document($branch, $path);
                return new \Tonic\Response(200, $document);
            } catch (\Exception $e) {
                throw new \Tonic\NotFoundException;
            }
        }
    }
}
