<?php

namespace Contentacle\Models;

class Repo extends Model
{
    private $git;

    function __construct($data, $gitProvider)
    {
        parent::__construct(array(
            'username' => true,
            'name' => true,
            'title' => 'Un-named repo',
            'url' => function ($data) {
                return '/users/'.$data['username'].'/repos/'.$data['name'];
            },
            'description' => true
        ), $data);

        $this->git = $gitProvider($data['username'], $data['name']);

    }
}