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
     * @method post
     * @accepts contentacle/repo+yaml
     * @accepts contentacle/repo+json
     * @provides application/hal+yaml
     * @provides application/hal+json
     * @secure
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
