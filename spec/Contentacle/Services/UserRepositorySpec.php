<?php

namespace spec\Contentacle\Services;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class UserRepositorySpec extends ObjectBehavior
{
    private $repoDir;

    function __construct()
    {
        $this->repoDir = sys_get_temp_dir().'/contentacle';
    }
    
    function let()
    {
        $this->beConstructedWith(
            $this->repoDir,
            function ($data) {
                return new \Contentacle\Models\User($data);
            }
        );
        @mkdir($this->repoDir);
        @mkdir($this->repoDir.'/cobb');
        file_put_contents($this->repoDir.'/cobb/profile.json', json_encode(array(
            'name' => 'Dominick Cobb',
            'username' => 'cobb',
            'password' => sha1('test')
        )));
    }

    function letgo()
    {
        unlink($this->repoDir.'/cobb/profile.json');
        rmdir($this->repoDir.'/cobb');
        @unlink($this->repoDir.'/eames/profile.json');
        @rmdir($this->repoDir.'/eames');
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Contentacle\Services\UserRepository');
    }

    function it_should_retrieve_users()
    {
        $this->getUsers()->shouldHaveCount(1);
        $user = $this->getUsers()['cobb'];
        $user->shouldHaveType('Contentacle\Models\User');
        $user->name->shouldBe('Dominick Cobb');
        $user->username->shouldBe('cobb');
    }

    function it_should_retrieve_a_given_user()
    {
        $user = $this->getUser('cobb');
        $user->shouldHaveType('Contentacle\Models\User');
        $user->name->shouldBe('Dominick Cobb');
        $user->username->shouldBe('cobb');
    }

    function it_should_create_a_new_user()
    {
        $user = $this->createUser(array(
            'username' => 'eames',
            'password' => 'test',
            'name' => 'Eames',
            'email' => 'eames@forger.com'
        ));
        $user->username->shouldBe('eames');
        $user->password->shouldBe(sha1('test'));
        $user->email->shouldBe('eames@forger.com');
    }

    function it_should_update_a_user()
    {
        $user = $this->getUser('cobb');
        $user = $this->updateUser($user, array(
            'username' => 'cobb',
            'password' => 'test',
            'email' => 'dominick@cobb.com'
        ));
        $user->username->shouldBe('cobb');
        $user->name->shouldBe('Dominick Cobb');
        $user->email->shouldBe('dominick@cobb.com');
    }

    function it_should_fail_to_write_a_user_profile_with_a_bad_password()
    {
        $this->shouldThrow('\Contentacle\Exceptions\ValidationException')->duringCreateUser(array(
            'username' => 'cobb',
            'password' => 'incorrect',
            'email' => 'dominick@cobb.com'
        ));
    }

    function it_should_fail_to_write_a_user_profile_with_no_password()
    {
        $this->shouldThrow('\Contentacle\Exceptions\ValidationException')->duringCreateUser(array(
            'username' => 'cobb',
            'email' => 'dominick@cobb.com'
        ));
    }

    function it_should_fail_to_write_a_user_profile_with_no_username()
    {
        $this->shouldThrow('\Contentacle\Exceptions\ValidationException')->duringCreateUser(array(
            'email' => 'dominick@cobb.com'
        ));
    }
}
