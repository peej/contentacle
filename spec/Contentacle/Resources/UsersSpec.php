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

        $user3 = (object)array(
            'username' => 'eames',
            'name' => 'Eames'
        );

        $userRepo->getUsers()->willReturn(array($user1, $user2));
        $userRepo->getUser('cobb')->willReturn($user1);
        $userRepo->getUser('arthur')->willReturn($user2);

        $userRepo->createUser(array(
            'username' => 'eames',
            'name' => 'Eames'
        ))->willReturn($user3);

        $exception = new \Contentacle\Exceptions\ValidationException;
        $exception->errors = array('username', 'password');
        $userRepo->createUser(array(
            'username' => '***'
        ))->willThrow($exception);
        
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
        $this->get()->body['_links']['cont:add-user']['content-type']->shouldContain('contentacle/user+yaml');
        $this->get()->body['_links']['cont:add-user']['content-type']->shouldContain('contentacle/user+json');
    }

    function it_should_get_a_list_of_users()
    {
        $this->get()->body['_embedded']['users']->shouldHaveCount(2);
        $this->get()->body['_embedded']['users'][0]['_links']['self']['href']->shouldBe('/users/cobb');
        $this->get()->body['_embedded']['users'][0]['username']->shouldBe('cobb');
        $this->get()->body['_embedded']['users'][0]['name']->shouldBe('Dominick Cobb');
        $this->get()->body['_embedded']['users'][1]['_links']['self']['href']->shouldBe('/users/arthur');
    }

    function it_should_create_a_user($request)
    {
        $request->getData()->willReturn(array(
            'username' => 'eames',
            'name' => 'Eames'
        ));

        $response = $this->createUser();

        $response->code->shouldBe(201);
        $response->location->shouldBe('/users/eames');
    }

    function it_should_fail_to_create_a_bad_user($request)
    {
        $request->getData()->willReturn(array(
            'username' => '***'
        ));

        $response = $this->createUser();

        $response->code->shouldBe(400);
        $response->contentType->shouldBe('application/hal');
        $response->body['_embedded']['errors'][0]['logref']->shouldBe('username');
        $response->body['_embedded']['errors'][1]['logref']->shouldBe('password');
    }
}