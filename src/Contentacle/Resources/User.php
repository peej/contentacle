<?php

namespace Contentacle\Resources;

/**
 * @uri /users/:username
 */
class User extends Resource
{

    /**
     * @provides contentacle/user+yaml
     * @provides contentacle/user+json
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
            $response->addForm('cont:edit-user', 'patch', null, array('application/json-patch+yaml', 'application/json-patch+json'), 'Edit the user');

            if ($this->embed) {
                foreach ($repoRepo->getRepos($user->username) as $repo) {
                    $response->embed('repos', $this->getChildResource('\Contentacle\Resources\Repo', array($user->username, $repo->name)));
                }
            }

            $response->contentType = 'contentacle/user+yaml';
            return $response;

        } catch (\Contentacle\Exceptions\UserException $e) {
            throw new \Tonic\NotFoundException;
        } catch (\Contentacle\Exceptions\RepoException $e) {
            throw new \Tonic\NotFoundException;
        }
    }

    /**
     * @method patch
     * @accepts application/json-patch+yaml
     * @accepts application/json-patch+json
     * @provides application/hal+yaml
     * @provides application/hal+json
     * @secure
     */
    public function updateUser($username)
    {
        $userRepo = $this->container['user_repository'];
        $repoRepo = $this->container['repo_repository'];

        try {
            $user = $userRepo->getUser($username);

            $userRepo->updateUser($user, $this->request->getData(), true);

            $response = new \Contentacle\Responses\Hal(200, $user);
            $response->contentType = 'contentacle/user+yaml';
            return $response;

        } catch (\Contentacle\Exceptions\UserException $e) {
            throw new \Tonic\NotFoundException;
        } catch (\Contentacle\Exceptions\RepoException $e) {
            throw new \Tonic\NotFoundException;
        }
    }

    /**
     * @method delete
     * @secure
     */
    public function deleteUser($username)
    {
        $userRepo = $this->container['user_repository'];
        
        try {
            $user = $userRepo->getUser($username);

            $userRepo->deleteUser($user);

            $response = new \Contentacle\Responses\Hal(204);

            return $response;

        } catch (\Contentacle\Exceptions\UserException $e) {
            throw new \Tonic\NotFoundException;
        }
    }
}
