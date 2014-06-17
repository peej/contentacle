<?php

namespace spec\Contentacle\Resources;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class UserSpec extends ObjectBehavior
{
    function let(\Tonic\Application $app, \Tonic\Request $request, \Pimple $pimple, \Contentacle\Services\UserRepository $userRepo, \Contentacle\Services\RepoRepository $repoRepo, \Contentacle\Models\User $user)
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
        
        $pimple->offsetGet('repo_repository')->willReturn($repoRepo);
        $pimple->offsetGet('user_repository')->willReturn($userRepo);

        $this->beConstructedWith($app, $request);
        $this->setContainer($pimple);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Contentacle\Resources\User');
    }

    function it_should_link_to_itself()
    {
        $this->get('cobb')->body['_links']['self']['href']->shouldBe('/users/cobb');
    }

    function it_should_link_to_edit_method() {
        $body = $this->get('cobb')->body;
        $body['_links']['cont:edit-user']['method']->shouldBe('patch');
        $body['_links']['cont:edit-user']['content-type']->shouldContain('application/json-patch+yaml');
        $body['_links']['cont:edit-user']['content-type']->shouldContain('application/json-patch+json');
    }

    function it_should_show_user_details()
    {
        $body = $this->get('cobb')->body;
        $body['username']->shouldBe('cobb');
        $body['name']->shouldBe('Dominick Cobb');
        $body['_embedded']['repos']->shouldBeArray();
        $body['_embedded']['repos'][0]['_links']['self']['href']->shouldBe('/users/cobb/repos/extraction');
        $body['_embedded']['repos'][0]['name']->shouldBe('extraction');
        $body['_embedded']['repos'][0]['username']->shouldBe('cobb');
        $body['_embedded']['repos'][0]['title']->shouldBe('Extraction 101');
        $body['_embedded']['repos'][0]['description']->shouldBe('Extraction instructions for Ariadne');
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
