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
        $response->addForm('cont:add-user', 'post', array('application/hal+yaml', 'application/hal+json'), 'Create a user');

        if ($this->embed) {
            foreach ($userRepo->getUsers() as $user) {
                $response->embed('users', $this->getChildResource('\Contentacle\Resources\User', array($user->username)));
            }
        }

        return $response;
    }

}