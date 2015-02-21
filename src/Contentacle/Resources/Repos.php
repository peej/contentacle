<?php

namespace Contentacle\Resources;

/**
 * @uri /users/:username/repos
 */
class Repos extends Resource
{
    protected function buildResponse($code, $templateName = null, $username = null)
    {
        $response = $this->response($code, $templateName);

        if ($username) {
            $response->addVar('username', $username);
            $response->addLink('self', '/users/'.$username.'/repos'.$this->formatExtension());
            $response->addLink('cont:doc', '/rels/repos');
        }

        return $response;
    }

    /**
     * Get a list of a users repositories.
     *
     * @method get
     * @response 200 OK
     * @provides application/hal+yaml
     * @provides application/hal+json
     * @provides text/html
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
        $response = $this->buildResponse(200, 'repos', $username);

        try {
            $search = isset($_GET['q']) ? $_GET['q'] : null;
            $repos = $this->repoRepository->getRepos($username, $search);

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
        $user = $this->userRepository->getUser($username);
        try {
            $repo = $this->repoRepository->createRepo($user, $this->request->getData());
            $response = $this->buildResponse(201);
            $response->location = '/users/'.$user->username.'/repos/'.$repo->name;

        } catch (\Contentacle\Exceptions\ValidationException $e) {
            $response = $this->buildResponse(400, 'repos', $username);
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
