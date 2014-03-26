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

    /**
     * Return a property of the model
     * @param str $name
     * @return mixed
     */
    public function prop($name)
    {
        return $this->$name;
    }
}