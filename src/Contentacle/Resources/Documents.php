<?php

namespace Contentacle\Resources;

/**
 * @uri /users/:username/repos/:repo/branches/:branch/documents
 * @uri /users/:username/repos/:repo/branches/:branch/documents/:path
 */
class Documents extends Resource {

    function get($username, $repoName, $branch, $path = null)
    {
        $repoRepo = $this->container['repo_repository'];

        if (isset($_SERVER['REQUEST_URI'])) {
            $path = substr($_SERVER['REQUEST_URI'], strlen('/users/'.$username.'/repos/'.$repoName.'/branches/'.$branch.'/documents/'));
        }
        
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
