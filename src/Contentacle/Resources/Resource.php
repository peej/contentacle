<?php

namespace Contentacle\Resources;

class Resource extends \Tonic\Resource
{
    protected $container;

    function setContainer($container)
    {
        $this->container = $container;
    }

    /**
     * @method get
     * @provides text/yaml
     * @provides application/json
     */
    function get() {}
    
    function secure()
    {
        
    }

}