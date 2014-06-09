<?php

namespace Contentacle\Models;

class User extends Model
{
    function __construct($data)
    {
        parent::__construct(array(
            'username' => true,
            'name' => 'Un-named user',
            'password' => true,
            'email' => function ($data) {
                return $data['username'].'@localhost';
            }
        ), $data);
    }
}