<?php

namespace Contentacle\Resources;

/**
 * @uri /users/:username/repos/:repo/branches
 */
class Branches extends Resource {

    /**
     * @provides application/hal+yaml
     * @provides application/hal+json
     */
    function get($username, $repoName)
    {
        try {
            $repoRepo = $this->container['repo_repository'];
            $repo = $repoRepo->getRepo($username, $repoName);
            
            $response = new \Contentacle\Responses\Hal();

            $response->addLink('self', '/users/'.$username.'/repos/'.$repoName.'/branches'.$this->formatExtension());
            $response->addForm('cont:create-branch', 'post', null, 'Create a branch');

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
