<?php

namespace Contentacle\Models;

class Model
{
    private $schema;
    private $data;

    function __construct($schema, $data)
    {
        $this->schema = $schema;

        $errors = array();
        foreach ($schema as $name => $scheme) {
            try {
                $this->setProp($name, isset($data[$name]) ? $data[$name] : null);
            } catch (\Contentacle\Exceptions\ValidationException $e) {
                $errors[] = $name;
            }
        }
        if ($errors) {
            $e = new \Contentacle\Exceptions\ValidationException;
            $e->errors = $errors;
            throw $e;
        }
    }

    function __get($name)
    {
        return $this->prop($name);
    }

    function __set($name, $value)
    {
        $this->setProp($name, $value);
    }

    /**
     * Return a property of the model
     * @param str $name
     * @return mixed
     */
    public function prop($name)
    {
        return isset($this->data[$name]) ? $this->data[$name] : null;
    }

    public function props() {
        return $this->data;
    }

    public function setProp($name, $value)
    {
        if (isset($this->schema[$name])) {
            if (is_bool($this->schema[$name])) {
                return $this->data[$name] = $value;
            } elseif (is_string($this->schema[$name]) && preg_match($this->schema[$name], $value)) {
                return $this->data[$name] = $value;
            } elseif (is_callable($this->schema[$name])) {
                return $this->data[$name] = $this->schema[$name]($value);
            }
            throw new \Contentacle\Exceptions\ValidationException();
        }
        return false;
    }

    public function setProps($data)
    {
        $errors = array();
        foreach ($data as $name => $value) {
            try {
                $this->setProp($name, $value);
            } catch (\Contentacle\Exceptions\ValidationException $e) {
                $errors[] = $name;
            }
        }
        if ($errors) {
            $e = new \Contentacle\Exceptions\ValidationException;
            $e->errors = $errors;
            throw $e;
        }
    }
}