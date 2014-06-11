<?php

namespace Contentacle\Resources;

/**
 * @uri /users/:username/repos
 */
class Repos extends Resource {

    /**
     * @provides application/hal+yaml
     * @provides application/hal+json
     */
    function get($username)
    {
        try {
            $repoRepo = $this->container['repo_repository'];
            $repos = $repoRepo->getRepos($username);

            $response = new \Contentacle\Responses\Hal();

            $response->addLink('self', '/users/'.$username.'/repos'.$this->formatExtension());
            $response->addForm('cont:create-repo', 'post', 'Create a repo');

            if ($this->embed) {
                foreach ($repos as $repo) {
                    $response->embed('repos', $this->getChildResource('\Contentacle\Resources\Repo', array($username, $repo->name)));
                }
            }
            
            return $response;

        } catch (\Contentacle\Exceptions\RepoException $e) {
            throw new \Tonic\NotFoundException;
        }
    }

}