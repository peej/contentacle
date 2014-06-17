<?php

namespace Contentacle\Models;

class User extends Model
{
    function __construct($data)
    {
        parent::__construct(array(
            'username' => '/^[a-z0-9]{2,40}$/',
            'name' => '/^[A-Za-z0-9 ]{2,40}$/',
            'password' => true,
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

    private function hashPassword($username, $password)
    {
        return sha1($username.':'.$password);
    }

    public function setPassword($password)
    {
        $this->setProp('password', $this->hashPassword($this->prop('username'), $password));
    }

    public function verifyPassword($password)
    {
        return $this->hashPassword($this->prop('username'), $password) == $this->prop('password');
    }
}