<?php

namespace spec\Contentacle\Models;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class UserSpec extends ObjectBehavior
{
    function let($store)
    {
        $userData = (object)array(
            'username' => 'peej',
            'password' => 'fbb6db29f4afb3787d1c4807b6d0765070565c15',
            'name' => 'Paul James'
        );
        
        $store->beADoubleOf('\Contentacle\Services\JsonStore');
        $store->load('peej')->willReturn($userData);
        $container['store'] = $store;

        @mkdir('/tmp/peej');
        @mkdir('/tmp/peej/test');
        $container['repo_dir'] = '/tmp';

        $this->beConstructedWith($container, 'peej');
    }

    function letgo()
    {
        rmdir('/tmp/peej/test');
        rmdir('/tmp/peej');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Contentacle\Models\User');
    }

    function it_should_load_user_data()
    {
        $this->username->shouldBe('peej');
        $password = sha1('test'.\Contentacle\Models\User::PASSWORD_SALT);
        $this->password->shouldBe($password);
        $this->name->shouldBe('Paul James');
    }

    function it_should_know_about_a_users_repos()
    {
        $this->repos->shouldHaveCount(1);
        $this->getRepo('test')->shouldHaveType('Contentacle\Models\Repo');
        $this->getRepo('nonexistant')->shouldBe(null);
    }

    function it_should_validate_a_users_credentials()
    {
        $this->validate('test')->shouldBe(true);
        $this->validate('wrong password')->shouldBe(false);
    }

    function it_should_check_a_client_cookie()
    {
        $this->auth(array(
            'username' => 'peej',
            'session' => sha1('peej'.sha1('test'.\Contentacle\Models\User::PASSWORD_SALT).'127.0.0.1')
        ))->shouldBe(true);
    }
}
