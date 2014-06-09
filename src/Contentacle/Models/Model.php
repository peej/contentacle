<?php

namespace Contentacle\Models;

class Model
{
    function __construct($schema, $data)
    {
        foreach ($schema as $name => $value) {
            if (isset($data[$name])) {
                $this->$name = $data[$name];
            } elseif (is_callable($value)) {
                $this->$name = $value($data);
            } else {
                $this->$name = $value;
            }
        }
    }

    function __get($name)
    {
        return $this->prop($name);
    }

    /**
     * Return a property of the model
     * @param str $name
     * @return mixed
     */
    public function prop($name)
    {
        return isset($this->$name) ? $this->$name : null;
    }

    public function props() {
        return get_object_vars($this);
    }
}