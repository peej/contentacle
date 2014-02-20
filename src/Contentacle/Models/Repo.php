<?php

namespace Contentacle\Models;

class Repo extends \Git\Repo
{
    public $name, $username;

    function __construct($repoPath)
    {
        $this->name = substr(basename($repoPath), 0, -4);
        $this->username = basename(dirname($repoPath));
        parent::__construct($repoPath);
    }
}