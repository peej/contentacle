<?php

namespace Contentacle\Resources;

/**
 * @uri /users/:username
 */
class User extends Resource
{

    /**
     * @provides application/hal+yaml
     * @provides application/hal+json
     */
    function get($username)
    {   
        $userRepo = $this->container['user_repository'];
        $repoRepo = $this->container['repo_repository'];

        try {
            $user = $userRepo->getUser($username);

            $response = new \Contentacle\Responses\Hal(200, $user);

            $response->addLink('self', '/users/'.$username.$this->formatExtension());
            $response->addLink('cont:repos', '/users/'.$username.'/repos'.$this->formatExtension());
            $response->addForm('cont:edit-user', 'patch', array('application/json-patch+yaml', 'application/json-patch+json'), 'Edit the user');

            if ($this->embed) {
                foreach ($repoRepo->getRepos($user->username) as $repo) {
                    $response->embed('repos', $this->getChildResource('\Contentacle\Resources\Repo', array($user->username, $repo->name)));
                }
            }

            return $response;

        } catch (\Contentacle\Exceptions\UserException $e) {
            throw new \Tonic\NotFoundException;
        } catch (\Contentacle\Exceptions\RepoException $e) {
            throw new \Tonic\NotFoundException;
        }
    }

}