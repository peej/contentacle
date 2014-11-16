<?php

namespace spec\Contentacle\Resources;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class UserSpec extends ObjectBehavior
{
    function let(\Tonic\Application $app, \Tonic\Request $request, \Contentacle\Models\User $user, \Contentacle\Services\UserRepository $userRepo, \Contentacle\Services\RepoRepository $repoRepo, \Contentacle\Services\Yaml $yaml)
    {
        $repo = (object)array(
            'name' => 'extraction',
            'username' => 'cobb',
            'title' => 'Extraction 101',
            'description' => 'Extraction instructions for Ariadne'
        );

        $repoRepo->getRepos('cobb')->willReturn(array(
            'extraction' => $repo
        ));
        $repoRepo->getRepo('cobb', 'extraction')->willReturn($repo);

        $user->prop('username')->willReturn('cobb');
        $user->prop('name')->willReturn('Dominick Cobb');
        $user->props()->willReturn(array(
            'username' => 'cobb',
            'name' => 'Dominick Cobb'
        ));
        $user->patch(array(
            'op' => 'replace',
            'path' => 'name',
            'value' => 'Cobb'
        ))->will(function () use ($user) {
            $user->prop('name')->willReturn('Cobb');
            $user->props()->willReturn(array(
                'username' => 'cobb',
                'name' => 'Cobb'
            ));
        });
        
        $userRepo->getUser('cobb')->willReturn($user);
        $userRepo->getUser(Argument::cetera())->willThrow(new \Contentacle\Exceptions\UserException);
        $userRepo->updateUser($user, Argument::any(), true)->will(function () use ($user) {
            $user->prop('name')->willReturn('Cobb');
            $user->props()->willReturn(array(
                'username' => 'cobb',
                'name' => 'Cobb'
            ));
            return $user;
        });
        $userRepo->deleteUser($user)->will(function () {
            $this->getUser('cobb')->willThrow('\Tonic\NotFoundException');
        });

        $this->beConstructedWith($app, $request);
        $this->setUserRepository($userRepo);
        $this->setRepoRepository($repoRepo);
        $this->setHalResponse(function($code = null, $body = null, $headers = array()) use ($yaml) {
            return new \Contentacle\Responses\Hal($yaml, $code, $body, $headers);
        });
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Contentacle\Resources\User');
    }

    function it_should_link_to_itself()
    {
        $response = $this->get('cobb');
        $response->body['_links']['self']['href']->shouldBe('/users/cobb');
    }

    function it_should_link_to_its_own_documentation()
    {
        $response = $this->get('cobb');
        $response->body['_links']['cont:doc']['href']->shouldBe('/rels/user');
    }

    function it_should_link_to_repos()
    {
        $response = $this->get('cobb');
        $response->body['_links']['cont:repos']['href']->shouldBe('/users/cobb/repos');
    }

    function it_should_show_user_details()
    {
        $response = $this->get('cobb');
        $response->body['username']->shouldBe('cobb');
        $response->body['name']->shouldBe('Dominick Cobb');
        $response->body['_embedded']['cont:repo']->shouldBeArray();
        $response->body['_embedded']['cont:repo'][0]['_links']['self']['href']->shouldBe('/users/cobb/repos/extraction');
        $response->body['_embedded']['cont:repo'][0]['name']->shouldBe('extraction');
        $response->body['_embedded']['cont:repo'][0]['username']->shouldBe('cobb');
        $response->body['_embedded']['cont:repo'][0]['title']->shouldBe('Extraction 101');
        $response->body['_embedded']['cont:repo'][0]['description']->shouldBe('Extraction instructions for Ariadne');
    }

    function it_should_error_for_unknown_user()
    {
        $this->shouldThrow('\Tonic\NotFoundException')->duringGet('ariadne');
    }

    function it_should_update_a_user($request)
    {
        $request->getData()->willReturn(array(
            'op' => 'replace',
            'path' => 'name',
            'value' => 'Cobb'
        ));

        $response = $this->updateUser('cobb');

        $response->code->shouldBe(200);
        $response->body['username']->shouldBe('cobb');
        $response->body['name']->shouldBe('Cobb');
    }

    function it_should_delete_a_user()
    {
        $response = $this->deleteUser('cobb');
        $response->code->shouldBe(204);

        $this->shouldThrow('\Tonic\NotFoundException')->duringGet('cobb');
    }

}
