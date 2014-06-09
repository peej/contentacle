<?php

namespace Contentacle\Resources;

/**
 * @uri /users/:username
 */
class User extends Resource
{

    /**
     * @provides text/yaml
     * @provides application/json
     */
    function get($username)
    {   
        $userRepo = $this->container['user_repository'];
        $repoRepo = $this->container['repo_repository'];

        try {
            $user = $userRepo->getUser($username);

            $response = new \Contentacle\Responses\Hal(200, $user);

            $response->addLink('self', '/users/'.$username.$this->formatExtension());
            $response->addLink('repos', '/users/'.$username.'/repos'.$this->formatExtension());
            $response->addForm('edit', 'put', array('contentacle/user+yaml', 'contentacle/user+json'), 'Edit the user');

            if ($this->embed) {
                foreach ($repoRepo->getRepos($user->username) as $repo) {
                    $response->embed('repos', $this->getChildResource('\Contentacle\Resources\Repo', array($user->username, $repo->name)));
                }
            }

            return $response;

        } catch (\Contentacle\Services\UserException $e) {
            throw new \Tonic\NotFoundException;
        } catch (\Contentacle\Services\RepoException $e) {
            throw new \Tonic\NotFoundException;
        }
    }

}