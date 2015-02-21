<?php

namespace Contentacle\Resources;

class Resource extends \Tonic\Resource
{
    private $extension = '';

    function __construct($deps)
    {
        parent::__construct($deps['app'], $deps['request']);
        $this->params['embed'] = true;

        foreach ($deps as $depName => $dep) {
            $this->$depName = $dep;
        }
    }

    function __call($method, $args) {
        if (is_callable($this->$method)) {
            return call_user_func_array($this->$method, $args);
        }
        throw new \Exception('Trying to access undefined dependancy "'.$method.'"');
    }

    protected function accepts($mimetype)
    {
        if ($mimetype == '*') return 1;
        return parent::accepts($mimetype);
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
        $resource = $this->resourceFactory($resourceName);
        $resource->embed = $embedChildren;
        $response = call_user_func_array(array($resource, 'get'), $parameters);
        return $response->data;
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
     * Discover which HTTP methods are allowed on this resource.
     *
     * @method options
     * @response 200 OK
     * @header Allow HTTP methods this resource allows.
     */
    function options() {
        $className = get_class($this);

        if (isset($this->app->resources[$className])) {
            $metadata = $this->app->resources[$className];
            $allow = array();

            foreach ($metadata->getMethods() as $methodData) {
                $allow = array_merge($allow, $methodData->getMethod());
            }

            $allow = array_unique($allow);

            $response = new \Tonic\Response(200);
            $response->allow = strtoupper(join(',', $allow));
        } else {
            $response = new \Tonic\Response(404);
        }
        return $response;
    }

    function secure()
    {
        if ($this->oauth->verifyToken()) {
            return;
        } elseif (
            isset($_SERVER['PHP_AUTH_USER']) && $_SERVER['PHP_AUTH_USER'] != '' &&
            isset($_SERVER['PHP_AUTH_PW']) && $_SERVER['PHP_AUTH_PW'] != '' &&
            isset($this->request->getParams()['username'])
        ) {
            $username = $this->request->getParams()['username'];
            if ($_SERVER['PHP_AUTH_USER'] == $username) {
                $user = $this->userRepository->getUser($username);
                if ($user->verifyPassword($_SERVER['PHP_AUTH_PW'])) {
                    return;
                }
            }
        }
        throw new \Tonic\UnauthorizedException;
    }

}