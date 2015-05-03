<?php

namespace Contentacle\Resources;

/**
 * @uri /users/:username/repos/:repo/new
 */
class NewBranch extends Resource
{
    /**
     * HTML form for creating a new branch.
     *
     * @method get
     * @response 200 OK
     * @provides text/html
     * @field name The name of the branch.
     * @field branch The existing branch to branch off of.
     * @links self Link to itself.
     * @links cont:doc Link to this documentation.
     * @secure
     */
    function get($username, $repoName)
    {
        $response = $this->response('200', 'new-branch');

        $this->configureResponse($response);

        $response->addLink('self', $this->buildUrl($username, $repoName, false, 'new'));
        $response->addLink('cont:branches', $this->buildUrl($username, $repoName, false, 'branches'));
        $response->addLink('up', $this->buildUrl($username, $repoName));

        $repo = $this->repoRepository->getRepo($username, $repoName);

        $response->addVar('branch', isset($_GET['branch']) ? $_GET['branch'] : null);
        $response->addVar('branches', $repo->branches());

        return $response;
    }
}