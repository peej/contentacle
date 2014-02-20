<?php

namespace Contentacle\Resources;

class Resource extends \Tonic\Resource
{

    function template($name)
    {
        $this->after(function ($response) use ($name) {
            $smarty = $this->app->container['smarty'];
            if (is_array($response->body)) {
                $smarty->assign($response->body);
            }
            $response->body = $smarty->fetch($name);
        });
    }

    function secure()
    {
        
    }

}