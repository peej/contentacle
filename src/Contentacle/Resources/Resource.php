<?php

namespace Contentacle\Resources;

abstract class Resource extends \Tonic\Resource
{
    private $deps;
    private $extension = '';

    function __construct($deps)
    {
        $this->deps = $deps;

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

    protected function getChildResource($resourceName, $parameters, $embedChildren = false)
    {
        $resource = new $resourceName($this->deps);
        $resource->embed = $embedChildren;
        $response = call_user_func_array(array($resource, 'get'), $parameters);
        return $response->data;
    }

    protected function buildUrl($username, $repoName = null, $branchName = null)
    {
        $url = '/users/'.$username;

        if (is_string($repoName)) {
            $url .= '/repos/'.$repoName;
        } elseif ($repoName) {
            $url .= '/repos';
        }

        if (is_string($branchName)) {
            $url .= '/branches/'.$branchName;
        } elseif ($branchName) {
            $url .= '/branches';
        }

        if (func_num_args() > 3) {
            for ($argNum = 3; $argNum < func_num_args(); $argNum++) {
                $arg = func_get_arg($argNum);
                if ($arg) {
                    $url .= '/'.$arg;
                }
            }
        }

        return $url;
    }

    protected function buildUrlWithFormat()
    {
        return call_user_func_array(array($this, 'buildUrl'), func_get_args()).$this->formatExtension();
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
     * Add user, repo and branch data to the response.
     */
    protected function configureResponse($response, $repo, $branchName)
    {
        $username = $repo->username;
        $repoName = strtolower($repo->name);

        $response->addVar('nav', true);

        $response->addData(array(
            'username' => $username,
            'repo' => $repoName,
            'branch' => $branchName
        ));

        $response->addLink('cont:user', $this->buildUrlWithFormat($username));
        $response->addLink('cont:repo', $this->buildUrlWithFormat($username, $repoName));
        $response->addLink('cont:branch', $this->buildUrlWithFormat($username, $repoName, $branchName));
        $response->addLink('cont:documents', $this->buildUrl($username, $repoName, $branchName, 'documents'));
        $response->addLink('cont:commits', $this->buildUrlWithFormat($username, $repoName, $branchName, 'commits'));
    }

    protected function configureResponseWithDocument($response, $repo, $branchName, $document)
    {
        $username = $repo->username;
        $repoName = strtolower($repo->name);

        $this->configureResponse($response, $repo, $branchName);

        $response->addLink('cont:history', $this->buildUrl($username, $repoName, $branchName, 'history', $document['path']));
        $response->addLink('cont:raw', $this->buildUrl($username, $repoName, $branchName, 'raw', $document['path']));
        $response->addLink('cont:document', $this->buildUrl($username, $repoName, $branchName, 'documents', $document['path']));
        $response->addLink('cont:edit', $this->buildUrl($username, $repoName, $branchName, 'edit', $document['path']));
        $response->addLink('cont:commit', $this->buildUrlWithFormat($username, $repoName, $branchName, 'commits', $document['commit']));

        if (isset($document['authorname'])) {
            $response->addLink('cont:author', $this->buildUrlWithFormat($document['authorname']));
        }

        $response->embed('cont:commit', $this->getChildResource('\Contentacle\Resources\Commit', array($username, $repoName, $branchName, $document['commit'])));
    }

    protected function fixPath($path, $username, $repoName, $branchName, $pathType = 'documents')
    {
        $requestUri = $_SERVER['SCRIPT_NAME'];
        $pathUri = '/users/'.$username.'/repos/'.$repoName.'/branches/'.$branchName.'/'.$pathType.'/';

        if ($path === true) {
            $path = '';
        } elseif (strpos($requestUri, $pathUri) === 0) {
            $path = substr($requestUri, strlen($pathUri));
        }
        if ($path == false) {
            $path = '';
        }
        return $path;
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