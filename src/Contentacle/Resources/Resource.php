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

    protected function formatExtension()
    {
        if (isset($this->request->accept[0])) {
            switch ($this->request->accept[0]) {
            case 'application/yaml':
            case 'text/yaml':
                $this->extension = '.yaml';
                break;
            case 'application/json':
            case 'text/json':
                $this->extension = '.json';
                break;
            }
        }
        return $this->extension;
    }

    /**
     * @method get
     */
    function get() {}
    
    function secure()
    {
        
    }

}