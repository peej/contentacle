<?php

namespace Contentacle\Resources;

class Resource extends \Tonic\Resource
{
    protected $container;
    private $extension = '';

    function __construct($app, $request)
    {
        parent::__construct($app, $request);
        $this->params['embed'] = true;
    }

    function setContainer($container)
    {
        $this->container = $container;
    }

    protected function provides($mimetype)
    {
        if (count($this->request->getAccept()) == 0) return 0;

        $match = null;
        $pos = 0;

        foreach ($this->request->getAccept() as $acceptMimetype) {
            if ($acceptMimetype == $mimetype) {
                $match = $pos;
                break;
            } else {
                $format = substr($acceptMimetype, strrpos($acceptMimetype, '/') + 1);
                if (substr($mimetype, -strlen($format)) == $format) {
                    $match = $pos;
                }
            }
            $pos++;
        }
        if ($match !== null) {
            $this->after(function ($response) use ($mimetype) {
                $response->contentType = $mimetype;
            });

            return count($this->request->getAccept()) - $match;
        }
    }

    protected function fixPath($path, $username, $repoName, $branchName, $pathType = 'documents')
    {
        if ($path === true) {
            $path = '';
        } elseif (isset($_SERVER['REQUEST_URI'])) {
            return substr($_SERVER['REQUEST_URI'], strlen('/users/'.$username.'/repos/'.$repoName.'/branches/'.$branchName.'/'.$pathType.'/'));
        }
        return $path;
    }

    protected function getChildResource($resourceName, $parameters, $embedChildren = false)
    {
        $resource = new $resourceName($this->app, $this->request);
        $resource->setContainer($this->container);
        $resource->embed = $embedChildren;
        $response = call_user_func_array(array($resource, 'get'), $parameters);
        return $response->body;
    }

    protected function formatExtension($prefix = '.')
    {
        if (isset($this->request->accept[0])) {
            switch ($this->request->accept[0]) {
            case 'application/yaml':
            case 'text/yaml':
                $this->extension = $prefix.'yaml';
                break;
            case 'application/json':
            case 'text/json':
                $this->extension = $prefix.'json';
                break;
            }
        }
        return $this->extension;
    }

    /**
     * @method get
     * @method options
     */
    function get() {
        return new Hal();
    }
    
    function secure()
    {
        if (
            isset($_SERVER['PHP_AUTH_USER']) && $_SERVER['PHP_AUTH_USER'] != '' &&
            isset($_SERVER['PHP_AUTH_PW']) && $_SERVER['PHP_AUTH_PW'] != '' &&
            isset($this->request->getParams()['username'])
        ) {
            $username = $this->request->getParams()['username'];
            if ($_SERVER['PHP_AUTH_USER'] == $username) {
                $userRepo = $this->container['user_repository'];
                $user = $userRepo->getUser($username);
                if ($user->verifyPassword($_SERVER['PHP_AUTH_PW'])) {
                    return;
                }
            }
        }
        throw new \Tonic\UnauthorizedException;
    }

}