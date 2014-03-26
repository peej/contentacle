<?php

namespace spec\Contentacle\Resources;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class UsersSpec extends ObjectBehavior
{
    function let(\Tonic\Application $app, \Tonic\Request $request, \Pimple $pimple, \Contentacle\Services\UserRepository $userRepo, \Contentacle\Models\User $user1, \Contentacle\Models\User $user2)
    {
        $user1->prop('url')->willReturn('/users/cobb');
        $user1->prop('username')->willReturn('cobb');
        $user1->prop('name')->willReturn('Dominick Cobb');

        $user2->prop('url')->willReturn('/users/arthur');
        $user2->prop('username')->willReturn('arthur');
        $user2->prop('name')->willReturn('Arthur');

        $userRepo->getUsers()->willReturn(array($user1, $user2));
        $pimple->offsetGet('user_repository')->willReturn($userRepo);
        
        $this->beConstructedWith($app, $request);
        $this->setContainer($pimple);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Contentacle\Resources\Users');
    }

    function it_should_get_a_list_of_users()
    {
        $this->get()->body->shouldHaveCount(2);
        $this->get()->body[0]->prop('url')->shouldBe('/users/cobb');
        $this->get()->body[0]->prop('username')->shouldBe('cobb');
        $this->get()->body[0]->prop('name')->shouldBe('Dominick Cobb');
        $this->get()->body[1]->prop('url')->shouldBe('/users/arthur');
    }
}
