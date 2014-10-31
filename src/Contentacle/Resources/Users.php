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
        $userRepo = $this->container['user_repository'];
        
        $response = new \Contentacle\Responses\Hal();

        $response->addLink('self', '/users'.$this->formatExtension());
        $response->addForm('cont:add-user', 'post', null, 'contentacle/user', 'Create a user');

        if ($this->embed) {

            $search = isset($_GET['q']) ? $_GET['q'] : null;

            foreach ($userRepo->getUsers($search) as $user) {
                $response->embed('users', $this->getChildResource('\Contentacle\Resources\User', array($user->username)));
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
        $userRepo = $this->container['user_repository'];

        try {
            $user = $userRepo->createUser($this->request->getData());
            $response = new \Contentacle\Responses\Hal(201);
            $response->location = '/users/'.$user->username;

        } catch (\Contentacle\Exceptions\ValidationException $e) {
            $response = new \Contentacle\Responses\Hal(400);
            $response->contentType = 'application/hal';
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