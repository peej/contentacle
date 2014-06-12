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
        if (count($this->request->accept) == 0) return 0;

        $altMimetype = null;
        if (substr($mimetype, -4) == 'yaml') {
            $altMimetype = 'text/yaml';
        } elseif (substr($mimetype, -4) == 'json') {
            $altMimetype = 'application/json';
        }

        $pos = array_search($mimetype, $this->request->accept);
        $altPos = array_search($altMimetype, $this->request->accept);
        if ($pos === FALSE && $altPos === FALSE) {
            if (in_array('*/*', $this->request->accept)) {
                return 0;
            } else {
                throw new \Tonic\NotAcceptableException('No matching method for response type "'.join(', ', $this->request->accept).'"');
            }
        } else {
            $this->after(function ($response) use ($mimetype) {
                $response->contentType = $mimetype;
            });
            return count($this->request->accept) - $pos;
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
     */
    function get() {}
    
    function secure()
    {
        
    }

}