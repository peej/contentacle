<?php

namespace spec\Contentacle\Resources;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class UsersSpec extends ObjectBehavior
{
    function let(\Tonic\Application $app, \Tonic\Request $request, \Pimple $pimple, \Contentacle\Services\UserRepository $userRepo)
    {
        $userRepo->getUsers()->willReturn([
            ['url' => '/users/cobb', 'username' => 'cobb', 'name' => 'Dominick Cobb'],
            ['url' => '/users/arthur', 'username' => 'arthur', 'name' => 'Arthur'],
            ['url' => '/users/ariadne', 'username' => 'ariadne', 'name' => 'Ariadne'],
            ['url' => '/users/eames', 'username' => 'eames', 'name' => 'Eames']
        ]);
        $pimple->offsetGet('user_repository')->willReturn($userRepo);
        $app->container = $pimple;
        $this->beConstructedWith($app, $request);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Contentacle\Resources\Users');
    }

    function it_should_get_a_list_of_users()
    {
        $this->get()->body->shouldBeArray();
        $this->get()->body->shouldHaveCount(4);
        $this->get()->body[0]['url']->shouldBe('/users/cobb');
        $this->get()->body[0]['username']->shouldBe('cobb');
        $this->get()->body[0]['name']->shouldBe('Dominick Cobb');
        $this->get()->body[1]['url']->shouldBe('/users/arthur');
    }
}
