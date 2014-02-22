<?php

namespace Contentacle\Models;

class Repo extends \Git\Repo
{
    public $name, $username;
    public $title, $description;

    function __construct($container, $repoPath)
    {
        parent::__construct($repoPath);
        
        $this->name = substr(basename($repoPath), 0, -4);
        $this->username = basename(dirname($repoPath));

        $metadata = $container['store']->getRepoMetadata($this->username, $this->name);
        $this->title = $metadata->title;
        $this->description = $metadata->description;
    }
}