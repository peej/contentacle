<?php

namespace Contentacle\Resources;

/**
 * @uri /users/:username/repos/:repo/branches/:branch/blobs/:sha
 */
class Blob extends Resource {

    function get($username, $repoName, $branch, $sha)
    {
        $repoRepo = $this->container['repo_repository'];
        $repo = $repoRepo->getRepo($username, $repoName);

        try {
            return $repo->blob($sha);
        } catch (\Contentacle\Exceptions\RepoException $e) {}
        throw new \Tonic\NotFoundException;
    }
}