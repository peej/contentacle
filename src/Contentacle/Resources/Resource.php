<?php

namespace Contentacle\Resources;

class Resource extends \Tonic\Resource
{
    protected $deps = array();
    private $extension = '';

    function __construct($app, $request)
    {
        parent::__construct($app, $request);
        $this->params['embed'] = true;
    }

    public function setDependancies($deps)
    {
        $this->deps = $deps;
    }

    private function setDependancy($name, $dep)
    {
        $this->deps[$name] = $dep;
    }

    private function getDependancy($name)
    {
        if (!isset($this->deps[$name])) {
            throw new \Exception('Dependancy '.$name.' not set.');
        }
        $dep = $this->deps[$name];
        if (is_callable($dep)) {
            $args = array_slice(func_get_args(), 1);
            return call_user_func_array($dep, $args);
        } else {
            return $dep;
        }
    }

    public function setYaml($dep) { $this->setDependancy('yaml', $dep); }
    protected function getYaml() { return $this->getDependancy('yaml'); }

    public function setUserRepository($dep) { $this->setDependancy('user_repository', $dep); }
    protected function getUserRepository() { return $this->getDependancy('user_repository'); }

    public function setRepoRepository($dep) { $this->setDependancy('repo_repository', $dep); }
    protected function getRepoRepository() { return $this->getDependancy('repo_repository'); }

    public function setHalResponse($dep) { $this->setDependancy('hal_response', $dep); }
    protected function createHalResponse($code = null, $vars = array()) { return $this->getDependancy('hal_response', $code, $vars); }

    public function setHtmlResponse($dep) { $this->setDependancy('html_response', $dep); }
    protected function createHtmlResponse($templateName) { return $this->getDependancy('html_response', $templateName); }

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
        $resource->setDependancies($this->deps);
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
        return $this->createHalResponse(404);
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
                $userRepo = $this->getUserRepository();
                $user = $userRepo->getUser($username);
                if ($user->verifyPassword($_SERVER['PHP_AUTH_PW'])) {
                    return;
                }
            }
        }
        throw new \Tonic\UnauthorizedException;
    }

}