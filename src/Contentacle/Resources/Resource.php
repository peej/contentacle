<?php

namespace Contentacle\Resources;

class Resource extends \Tonic\Resource
{
    protected $container;

    function setContainer($container)
    {
        $this->container = $container;
    }

    protected function fixPath($path, $username, $repoName, $branch, $pathType = 'documents')
    {
        if (isset($_SERVER['REQUEST_URI'])) {
            return substr($_SERVER['REQUEST_URI'], strlen('/users/'.$username.'/repos/'.$repoName.'/branches/'.$branch.'/'.$pathType.'/'));
        }
        return $path;
    }

    /**
     * @method get
     */
    function get() {}
    
    function secure()
    {
        
    }

}