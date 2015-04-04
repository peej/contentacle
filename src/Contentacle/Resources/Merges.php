<?php

namespace Contentacle\Resources;

/**
 * @uri /users/:username/repos/:repo/branches/:branch/merges
 */
class Merges extends Resource
{
    /**
     * Get a list of merges that can be performed on this branch.
     *
     * @method get
     * @response 200 OK
     * @provides application/hal+yaml
     * @provides application/hal+json
     * @links self Link to itself
     * @links cont:doc Link to this documentation.
     * @links cont:merge Link to merges that can be performed.
     */
    function get($username, $repoName, $branchName)
    {
        try {
            $repo = $this->repoRepository->getRepo($username, $repoName);
            if (!$repo->hasBranch($branchName)) {
                throw new \Tonic\NotFoundException;
            }
        } catch (\Git\Exception $e) {
            throw new \Tonic\NotFoundException;
        }
        
        $response = $this->response(200, 'merges');

        $this->configureResponseWithBranch($response, $repo, $branchName);

        $response->addLink('self', $this->buildUrlWithFormat($username, $repoName, $branchName, 'merges'));
        $response->addLink('cont:doc', '/rels/merges');

        foreach ($repo->branches() as $branch) {
            if ($branchName != $branch) {
                $response->addLink(
                    'cont:merge',
                    $this->buildUrl($username, $repoName, $branchName, 'merges', $branch),
                    false,
                    $branch
                );
            }
        }

        return $response;

    }
}
