<?php

namespace Contentacle\Services;

class FileAccess
{
    /**
     * Read a file
     * @param str $path
     * @return str
     */
    function read($path)
    {
        return file_get_contents($path);
    }

    /**
     * Write a file
     * @param str $path
     * @param str $contents
     * @return bool
     */
    function write($path, $contents)
    {
        return file_put_contents($path, $contents) !== FALSE;
    }
}