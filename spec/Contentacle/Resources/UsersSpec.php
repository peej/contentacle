<?php

namespace spec\Contentacle\Resources;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class UsersSpec extends ObjectBehavior
{
    function let(\Tonic\Application $app, \Tonic\Request $request, \Pimple $pimple, \Contentacle\Services\UserRepository $userRepo)
    {
        $user1 = (object)array(
            'username' => 'cobb',
            'name' => 'Dominick Cobb'
        );

        $user2 = (object)array(
            'username' => 'arthur',
            'name' => 'Arthur'
        );

        $userRepo->getUsers()->willReturn(array($user1, $user2));
        $userRepo->getUser('cobb')->willReturn($user1);
        $userRepo->getUser('arthur')->willReturn($user2);
        $pimple->offsetGet('user_repository')->willReturn($userRepo);

        $pimple->offsetGet('repo_repository')->willReturn();
        
        $this->beConstructedWith($app, $request);
        $this->setContainer($pimple);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Contentacle\Resources\Users');
    }

    function it_should_link_to_itself()
    {
        $this->get()->body['_links']['self']['href']->shouldBe('/users');
    }

    function it_should_link_to_add_method() {
        $this->get()->body['_links']['cont:add-user']['method']->shouldBe('post');
        $this->get()->body['_links']['cont:add-user']['content-type']->shouldContain('application/hal+yaml');
        $this->get()->body['_links']['cont:add-user']['content-type']->shouldContain('application/hal+json');
    }

    function it_should_get_a_list_of_users()
    {
        $this->get()->body['_embedded']['users']->shouldHaveCount(2);
        $this->get()->body['_embedded']['users'][0]['_links']['self']['href']->shouldBe('/users/cobb');
        $this->get()->body['_embedded']['users'][0]['username']->shouldBe('cobb');
        $this->get()->body['_embedded']['users'][0]['name']->shouldBe('Dominick Cobb');
        $this->get()->body['_embedded']['users'][1]['_links']['self']['href']->shouldBe('/users/arthur');
    }
}
