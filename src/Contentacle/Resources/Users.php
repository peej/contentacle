<?php

namespace Contentacle\Resources;

/**
 * @uri /users
 */
class Users extends Resource
{
    /**
     * Get a list of users.
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
     * @embeds cont:user The list of users.
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
     * Create a user.
     *
     * @method post
     * @accepts application/hal+yaml
     * @accepts application/hal+json
     * @accepts application/yaml
     * @accepts application/json
     * @field username Username
     * @field name Users real name
     * @field password Password
     * @field email Email address
     * @response 201 Created
     * @response 400 Bad request
     * @provides application/hal+yaml
     * @provides application/hal+json
     * @header Location The URL of the created user.
     * @embeds cont:error A list of errored fields.
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
                $response->embed('cont:error', array(
                    'logref' => $field,
                    'message' => '"'.$field.'" field failed validation'
                ));
            }
        }

        return $response;
    }

}