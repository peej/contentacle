<?php

namespace spec\Contentacle\Resources;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class UsersSpec extends ObjectBehavior
{
    function let(\Tonic\Application $app, \Tonic\Request $request, \Contentacle\Services\UserRepository $userRepo, \Contentacle\Services\RepoRepository $repoRepo, \Contentacle\Services\Yaml $yaml)
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

        $userRepo->getUsers(null)->willReturn(array($user1, $user2));
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

        $this->beConstructedWith($app, $request);
        $this->setUserRepository($userRepo);
        $this->setRepoRepository($repoRepo);
        $this->setHalResponse(function($code = null, $body = null, $headers = array()) use ($yaml) {
            return new \Contentacle\Responses\Hal($yaml, $code, $body, $headers);
        });
    }
    function it_is_initializable()
    {
        $this->shouldHaveType('Contentacle\Resources\Users');
    }

    function it_should_link_to_itself()
    {
        $response = $this->get();
        $response->body['_links']['self']['href']->shouldBe('/users');
    }

    function it_should_link_to_its_own_documentation()
    {
        $response = $this->get();
        $response->body['_links']['cont:doc']['href']->shouldBe('/rels/users');
    }

    function it_should_get_a_list_of_users()
    {
        $response = $this->get();
        $response->body['_embedded']['cont:user']->shouldHaveCount(2);
        $response->body['_embedded']['cont:user'][0]['_links']['self']['href']->shouldBe('/users/cobb');
        $response->body['_embedded']['cont:user'][0]['username']->shouldBe('cobb');
        $response->body['_embedded']['cont:user'][0]['name']->shouldBe('Dominick Cobb');
        $response->body['_embedded']['cont:user'][1]['_links']['self']['href']->shouldBe('/users/arthur');
        $response->body['_embedded']['cont:user'][1]['username']->shouldBe('arthur');
        $response->body['_embedded']['cont:user'][1]['name']->shouldBe('Arthur');
    }

    function it_should_create_a_user($request)
    {
        $request->getData()->willReturn(array(
            'username' => 'eames',
            'name' => 'Eames'
        ));

        $response = $this->createUser();

        $response->getCode()->shouldBe(201);
        $response->location->shouldBe('/users/eames');
    }

    function it_should_fail_to_create_a_bad_user($request)
    {
        $request->getData()->willReturn(array(
            'username' => '***'
        ));

        $response = $this->createUser();

        $response->getCode()->shouldBe(400);
        $response->contentType->shouldBe('application/hal+yaml');
        $response->body['_embedded']['cont:error'][0]['logref']->shouldBe('username');
        $response->body['_embedded']['cont:error'][1]['logref']->shouldBe('password');
    }
}