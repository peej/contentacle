<?php

namespace Contentacle\Resources;

/**
 * @uri /users/:username
 */
class User extends Resource
{
    /**
     * Get a user.
     *
     * @method get
     * @response 200 OK
     * @provides application/hal+yaml
     * @provides application/hal+json
     * @field username Username
     * @field name Users real name
     * @field password Password
     * @field email Email address
     * @links self Link to itself
     * @links cont:doc Link to this documentation.
     * @links cont:repos Link to the users repositories.
     * @embeds cont:repo A list of the users repositories.
     */
    function get($username)
    {   
        $userRepo = $this->getUserRepository();
        $repoRepo = $this->getRepoRepository();

        try {
            $user = $userRepo->getUser($username);

            $response = $this->createHalResponse(200, $user);

            $response->addLink('self', '/users/'.$username.$this->formatExtension());
            $response->addLink('cont:doc', '/rels/user');
            $response->addLink('cont:repos', '/users/'.$username.'/repos'.$this->formatExtension());

            if ($this->embed) {
                foreach ($repoRepo->getRepos($user->username) as $repo) {
                    $response->embed('cont:repo', $this->getChildResource('\Contentacle\Resources\Repo', array($user->username, $repo->name)));
                }
            }

            return $response;

        } catch (\Contentacle\Exceptions\UserException $e) {
            throw new \Tonic\NotFoundException;
        } catch (\Contentacle\Exceptions\RepoException $e) {
            throw new \Tonic\NotFoundException;
        }
    }

    /**
     * Update a user.
     *
     * @method patch
     * @accepts application/json-patch+yaml
     * @accepts application/json-patch+json
     * @secure
     * @field username Username
     * @field name Users real name
     * @field password Password
     * @field email Email address
     * @response 200 OK
     * @provides application/hal+yaml
     * @provides application/hal+json
     * @links self Link to itself
     * @links cont:doc Link to this documentation.
     * @links cont:repos Link to the users repositories.
     * @embeds cont:repo A list of the users repositories.
     */
    public function updateUser($username)
    {
        $userRepo = $this->getUserRepository();
        $repoRepo = $this->getRepoRepository();

        try {
            $user = $userRepo->getUser($username);

            $userRepo->updateUser($user, $this->request->getData(), true);

            return $this->createHalResponse(200, $user);

        } catch (\Contentacle\Exceptions\UserException $e) {
            throw new \Tonic\NotFoundException;
        } catch (\Contentacle\Exceptions\RepoException $e) {
            throw new \Tonic\NotFoundException;
        }
    }

    /**
     * Delete a user.
     *
     * @method delete
     * @secure
     * @response 204 No content
     */
    public function deleteUser($username)
    {
        $userRepo = $this->getUserRepository();
        
        try {
            $user = $userRepo->getUser($username);

            $userRepo->deleteUser($user);

            return $this->createHalResponse(204);

        } catch (\Contentacle\Exceptions\UserException $e) {
            throw new \Tonic\NotFoundException;
        }
    }
}
