<?php

namespace Contentacle\Resources;

class WithinDocument extends WithinBranch
{
    protected function configureResponseWithDocument($response, $repo, $branchName, $document)
    {
        $username = $repo->username;
        $repoName = strtolower($repo->name);

        $this->configureResponse($response, $repo, $branchName);

        $response->addData($document);

        $response->addLink('cont:history', $this->buildUrl($username, $repoName, $branchName, 'history', $document['path']));
        $response->addLink('cont:raw', $this->buildUrl($username, $repoName, $branchName, 'raw', $document['path']));
        $response->addLink('cont:document', $this->buildUrl($username, $repoName, $branchName, 'documents', $document['path']));
        $response->addLink('cont:edit', $this->buildUrl($username, $repoName, $branchName, 'edit', $document['path']));
        $response->addLink('cont:commit', $this->buildUrlWithFormat($username, $repoName, $branchName, 'commits', $document['commit']));

        if ($document['username']) {
            $response->addLink('cont:author', $this->buildUrlWithFormat($document['username']));
        }

        $response->embed('cont:commit', $this->getChildResource('\Contentacle\Resources\Commit', array($username, $repoName, $branchName, $document['commit'])));
    }

    protected function fixPath($path, $username, $repoName, $branchName, $pathType = 'documents')
    {
        if ($path === true) {
            $path = '';
        } elseif (isset($_SERVER['REQUEST_URI'])) {
            $path = substr($_SERVER['REQUEST_URI'], strlen('/users/'.$username.'/repos/'.$repoName.'/branches/'.$branchName.'/'.$pathType.'/'));
        }
        if ($path === false) {
            $path = '';
        }
        return $path;
    }
}