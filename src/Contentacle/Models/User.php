<?php

namespace Contentacle\Models;

class User extends Model
{
    function __construct($data)
    {
        parent::__construct(array(
            'username' => '/^[a-z0-9]{2,40}$/',
            'name' => '/^[A-Za-z0-9 ]{2,40}$/',
            'password' => function ($value) {
                return sha1($value);
            },
            'email' => function ($value) use ($data) {
                if ($value) {
                    if (preg_match('/^.+@.+$/', $value)) {
                        return $value;
                    } else {
                        throw new \Contentacle\Exceptions\ValidationException();
                    }
                }
                return $data['username'].'@localhost';
            }
        ), $data);
    }
}