<?php

namespace Contentacle\Resources;

/**
 * @uri /users/:username
 */
class User extends Resource
{
    private function buildResponse($user)
    {
        $response = $this->response(200, 'user');

        $user->password = ''; // hide hashed user password from being output.

        $response->addData($user);
        $response->addVar('title', $user->username.' ('.$user->name.')');
        $response->addLink('self', '/users/'.$user->username.$this->formatExtension());
        $response->addLink('cont:doc', '/rels/user');
        $response->addLink('cont:repos', '/users/'.$user->username.'/repos'.$this->formatExtension());

        if ($this->embed) {
            foreach ($this->repoRepository->getRepos($user->username) as $repo) {
                $response->embed('cont:repo', $this->getChildResource('\Contentacle\Resources\Repo', array($user->username, $repo->name)));
            }
        }

        return $response;
    }

    /**
     * Get a user.
     *
     * @method get
     * @response 200 OK
     * @provides application/hal+yaml
     * @provides application/hal+json
     * @provides text/html
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
        try {
            $user = $this->userRepository->getUser($username);

            return $this->buildResponse($user);

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
        try {
            $user = $this->userRepository->getUser($username);

            $this->userRepository->updateUser($user, $this->request->getData(), true);

            return $this->buildResponse($user);

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
        try {
            $user = $this->userRepository->getUser($username);

            $this->userRepository->deleteUser($user);

            return new \Tonic\Response(204);

        } catch (\Contentacle\Exceptions\UserException $e) {
            throw new \Tonic\NotFoundException;
        }
    }
}
