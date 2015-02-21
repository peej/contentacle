<?php

namespace Contentacle\Resources;

/**
 * @uri /users/:username/repos/:repo/branches/:branch/blobs/:sha
 */
class Blob extends Resource {

    /**
     * @method get
     */
    function get($username, $repoName, $branch, $sha)
    {
        $repo = $this->repoRepository->getRepo($username, $repoName);

        try {
            return $repo->blob($sha);
        } catch (\Contentacle\Exceptions\RepoException $e) {}
        throw new \Tonic\NotFoundException;
    }
}