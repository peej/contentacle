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
            'username' => 'cobb'
        )));
    }

    function letgo()
    {
        unlink($this->repoDir.'/cobb/profile.json');
        rmdir($this->repoDir.'/cobb');
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
}
