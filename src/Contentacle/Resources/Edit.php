<?php

namespace Contentacle\Resources;

/**
 * @uri /users/:username/repos/:repo/branches/:branch/edit/?(.*)$
 */
class Edit extends Document
{
    /**
     * Get the document edit form
     *
     * @method get
     * @response 200 OK
     * @provides text/html
     */
    function get($username, $repoName, $branchName, $path = null)
    {
        $repo = $this->repoRepository->getRepo($username, $repoName);
        try {
            $path = $this->fixPath($path, $username, $repoName, $branchName, 'edit');
            $document = $repo->document($branchName, $path);
            $response = $this->documentResponse('edit', $username, $repoName, $branchName, $document);

            $response->addVar('footer', false);

            return $response;

        } catch (\Contentacle\Exceptions\RepoException $e) {
            throw new \Tonic\NotFoundException;
        }
    }

}