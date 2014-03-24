<?php

namespace Contentacle\Resources;

class Resource extends \Tonic\Resource
{
    /**
     * @method get
     * @provides text/yaml
     */
    function getYaml() {
        return call_user_method_array('get', $this, func_get_args());
    }

    /**
     * @method get
     * @provides application/json
     */
    function getJson() {
        return call_user_method_array('get', $this, func_get_args());
    }

    function secure()
    {
        
    }

}