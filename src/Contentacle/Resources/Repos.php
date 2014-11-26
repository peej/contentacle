<?php

namespace Contentacle\Resources;

/**
 * @uri /users/:username/repos
 */
class Repos extends Resource
{
    /**
     * Get a list of a users repositories.
     *
     * @method get
     * @response 200 OK
     * @provides application/hal+yaml
     * @provides application/hal+json
     * @field name The short name of the repo.
     * @field title The display name of the repo.
     * @field description A description of the repo.
     * @field username The owner of the repo.
     * @links self Link to itself.
     * @links cont:doc Link to this documentation
     * @embeds cont:repo The list of repositories
     */
    function get($username)
    {
        $response = $this->createHalResponse();

        $response->addLink('self', '/users/'.$username.'/repos'.$this->formatExtension());
        $response->addLink('cont:doc', '/rels/repos');

        try {
            $repoRepo = $this->getRepoRepository();
            $search = isset($_GET['q']) ? $_GET['q'] : null;
            $repos = $repoRepo->getRepos($username, $search);

            if ($this->embed) {
                foreach ($repos as $repo) {
                    $response->embed('cont:repo', $this->getChildResource('\Contentacle\Resources\Repo', array($username, $repo->name)));
                }
            }
            
            return $response;

        } catch (\Contentacle\Exceptions\ValidationException $e) {
            return $response;

        } catch (\Contentacle\Exceptions\RepoException $e) {
            throw new \Tonic\NotFoundException;
        }
    }

    /**
     * Create a repository.
     *
     * @method post
     * @field name The short name of the repo.
     * @field title The display name of the repo.
     * @field description A description of the repo.
     * @accepts application/hal+yaml
     * @accepts application/hal+json
     * @accepts application/yaml
     * @accepts application/json
     * @secure
     * @response 201 Created
     * @response 400 Bad request
     * @provides application/hal+yaml
     * @provides application/hal+json
     * @header Location The URL of the created repository.
     * @embeds cont:error A list of errored fields.
     */
    public function createRepo($username)
    {
        $userRepo = $this->getUserRepository();
        $repoRepo = $this->getRepoRepository();

        $user = $userRepo->getUser($username);
        try {
            $repo = $repoRepo->createRepo($user, $this->request->getData());
            $response = $this->createHalResponse(201);
            $response->location = '/users/'.$user->username.'/repos/'.$repo->name;

        } catch (\Contentacle\Exceptions\ValidationException $e) {
            $response = $this->createHalResponse(400);
            foreach ($e->errors as $field) {
                $response->embed('errors', array(
                    'logref' => $field,
                    'message' => '"'.$field.'" field failed validation'
                ));
            }
        }

        return $response;
    }
}
