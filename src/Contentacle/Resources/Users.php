<?php

namespace Contentacle\Resources;

/**
 * @uri /users
 */
class Users extends Resource
{
    /**
     * @provides application/hal+yaml
     * @provides application/hal+json
     */
    function get()
    {
        $userRepo = $this->getUserRepository();
        
        $response = $this->createHalResponse();

        $response->addLink('self', '/users'.$this->formatExtension());
        $response->addLink('cont:doc', '/rels/users');

        if ($this->embed) {

            $search = isset($_GET['q']) ? $_GET['q'] : null;

            foreach ($userRepo->getUsers($search) as $user) {
                $response->embed('cont:user', $this->getChildResource('\Contentacle\Resources\User', array($user->username)));
            }
        }

        return $response;
    }
    
    /**
     * @method post
     * @accepts contentacle/user+yaml
     * @accepts contentacle/user+json
     * @provides application/hal+yaml
     * @provides application/hal+json
     */
    function createUser()
    {
        $userRepo = $this->getUserRepository();

        try {
            $user = $userRepo->createUser($this->request->getData());
            $response = $this->createHalResponse(201);
            $response->location = '/users/'.$user->username;

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