<?php

namespace Contentacle\Services;

class Yaml extends \Spyc
{
    /**
     * Encode a variable into a Yaml string. Turn PHP objects in arrays in the process.
     * @param mixed $data
     * @return str
     */
    function encode($data)
    {
        return @parent::YAMLDump($this->toArray($data));
    }

    /**
     * Recursive function to cast nested objects into arrays
     */
    private function toArray($elem)
    {
        if (is_object($elem)) {
            if ($elem instanceof \Contentacle\Models\Model) {
                $array = array();
                foreach (get_object_vars($elem) as $key => $value) { // don't just cast as we only want public members
                    $array[$key] = $value;
                }
                $elem = $array;
            } else {
                $elem = null;
            }
        }
        if (is_array($elem)) {
            foreach ($elem as $key => $value) {
                $elem[$key] = $this->toArray($value);
            }
        }
        return $elem;
    }

    /**
     * Decode a Yaml string into a PHP array.
     * @param str $string
     * @return mixed
     */
    function decode($string)
    {
        return @parent::YAMLLoadString($string);
    }
}