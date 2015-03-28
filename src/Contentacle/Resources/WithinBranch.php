<?php

namespace Contentacle\Resources;

abstract class WithinBranch extends Resource
{
    /**
     * Add user, repo and branch data to the response.
     */
    protected function configureResponse($response, $repo, $branchName)
    {
        $username = $repo->username;
        $repoName = strtolower($repo->name);

        $response->addVar('nav', true);

        $response->addData(array(
            'username' => $username,
            'repo' => $repoName,
            'branch' => $branchName
        ));

        $response->addLink('cont:user', $this->buildUrlWithFormat($username));
        $response->addLink('cont:repo', $this->buildUrlWithFormat($username, $repoName));
        $response->addLink('cont:branch', $this->buildUrlWithFormat($username, $repoName, $branchName));
        $response->addLink('cont:documents', $this->buildUrl($username, $repoName, $branchName, 'documents'));
        $response->addLink('cont:commits', $this->buildUrlWithFormat($username, $repoName, $branchName, 'commits'));
    }

}