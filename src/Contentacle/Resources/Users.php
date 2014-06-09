<?php

namespace Contentacle\Resources;

/**
 * @uri /users
 */
class Users extends Resource
{
    /**
     * @provides text/yaml
     * @provides application/json
     */
    function get()
    {
        $userRepo = $this->container['user_repository'];
        
        $response = new \Contentacle\Responses\Hal();

        $response->addLink('self', '/users'.$this->formatExtension());
        $response->addForm('add', 'post', array('contentacle/user+yaml', 'contentacle/user+json'), 'Create a user');

        if ($this->embed) {
            foreach ($userRepo->getUsers() as $user) {
                $response->embed('users', $this->getChildResource('\Contentacle\Resources\User', array($user->username)));
            }
        }

        return $response;
    }

}