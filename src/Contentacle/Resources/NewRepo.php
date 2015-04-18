<?php

namespace Contentacle\Resources;

/**
 * @uri /users/:username/new
 */
class NewRepo extends Resource
{
    /**
     * HTML form for creating a new repository.
     *
     * @method get
     * @response 200 OK
     * @provides text/html
     * @field name The short name of the repo.
     * @field description A description of the repo.
     * @links self Link to itself.
     * @links cont:doc Link to this documentation.
     * @secure
     */
    function get($username)
    {
        $response = $this->response('200', 'new-repo');

        $this->configureResponse($response);

        $response->addLink('self', $this->buildUrl($username, false, false, 'new'));
        $response->addLink('cont:repos', $this->buildUrl($username, false, false, 'repos'));
        $response->addLink('up', $this->buildUrl($username));

        return $response;
    }
}