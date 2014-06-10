<?php

namespace Contentacle\Resources;

/**
 * @uri /users/:username/repos/:repoName
 */
class Repo extends Resource
{

    /**
     * @provides text/yaml
     * @provides application/json
     */
    function get($username, $repoName)
    {
        try {
            $repoRepo = $this->container['repo_repository'];
            $repo = $repoRepo->getRepo($username, $repoName);
            
            $response = new \Contentacle\Responses\Hal(200, $repo);

            $response->addLink('self', '/users/'.$username.'/repos/'.$repoName.$this->formatExtension());
            $response->addLink('branches', '/users/'.$username.'/repos/'.$repoName.'/branches'.$this->formatExtension());
            $response->addForm('edit', 'patch', array('contentacle/repo+yaml', 'contentacle/repo+json'), 'Edit the repo');
            $response->addForm('delete', 'delete', null, 'Remove the repo');

            if ($this->embed) {
                foreach ($repo->branches() as $branchName) {
                    $response->embed('branches', $this->getChildResource('\Contentacle\Resources\Branch', array($username, $repoName, $branchName)));
                }
            }
            
            return $response;

        } catch (\Git\Exception $e) {
            throw new \Tonic\NotFoundException;
        }
    }
}
