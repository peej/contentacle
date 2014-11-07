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
        $response = new \Contentacle\Responses\Hal();

        $response->addLink('self', '/users/'.$username.'/repos'.$this->formatExtension());
        $response->addForm('cont:create-repo', 'post', null, array('contentacle/repo+yaml', 'contentacle/repo+json'), 'Create a repo');

        try {
            $repoRepo = $this->container['repo_repository'];
            $search = isset($_GET['q']) ? $_GET['q'] : null;
            $repos = $repoRepo->getRepos($username, $search);

            if ($this->embed) {
                foreach ($repos as $repo) {
                    $response->embed('repos', $this->getChildResource('\Contentacle\Resources\Repo', array($username, $repo->name)));
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
        $userRepo = $this->container['user_repository'];
        $repoRepo = $this->container['repo_repository'];

        $user = $userRepo->getUser($username);
        try {
            $repo = $repoRepo->createRepo($user, $this->request->getData());
            $response = new \Contentacle\Responses\Hal(201);
            $response->location = '/users/'.$user->username.'/repos/'.$repo->name;

        } catch (\Contentacle\Exceptions\ValidationException $e) {
            $response = new \Contentacle\Responses\Hal(400);
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
