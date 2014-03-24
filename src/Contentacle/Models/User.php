<?php

namespace Contentacle\Models;

class User extends Model
{
    function __construct($data)
    {
        parent::__construct(array(
            'username' => true,
            'name' => 'Un-named user',
            'url' => function ($data) {
                return '/users/'.$data['username'];
            },
            'password' => true,
            'email' => function ($data) {
                return $data['username'].'@localhost';
            }
        ), $data);
    }
}