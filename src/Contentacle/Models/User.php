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

    public function setPassword($password)
    {
        $this->setProp('password', $this->hashPassword($password));
    }

    private function hashPassword($password)
    {
        if (function_exists('password_hash')) {
            return password_hash($this->prop('username').':'.$password, PASSWORD_DEFAULT);
        }
        return sha1($this->prop('username').':'.$password);
    }

    public function verifyPassword($password)
    {
        if (function_exists('password_verify')) {
            return password_verify($this->prop('username').':'.$password, $this->prop('password'));
        }
        return $this->hashPassword($password) === $this->prop('password');
    }
}