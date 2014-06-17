<?php

namespace spec\Contentacle\Models;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class UserSpec extends ObjectBehavior
{
    function let()
    {
        $this->beConstructedWith(array(
            'username' => 'cobb',
            'name' => 'Dominick Cobb',
            'password' => 'test'
        ));
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Contentacle\Models\User');
    }

    function it_contains_user_metadata()
    {
        $this->username->shouldBe('cobb');
        $this->name->shouldBe('Dominick Cobb');
        $this->password->shouldBe('test');
        $this->email->shouldBe('cobb@localhost');
    }
}
