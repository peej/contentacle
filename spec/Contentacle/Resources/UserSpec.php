<?php

namespace spec\Contentacle\Resources;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class UserSpec extends ObjectBehavior
{
    function let(\Tonic\Application $app, \Tonic\Request $request, \Pimple $pimple, \Contentacle\Services\UserRepository $userRepo, \Contentacle\Models\User $user, \Contentacle\Services\RepoRepository $repoRepo, \Contentacle\Models\Repo $repo)
    {
        $repo->prop('url')->willReturn('/users/cobb/repos/extraction');
        $repo->prop('username')->willReturn('cobb');
        $repo->prop('name')->willReturn('Extraction 101');
        $repo->prop('description')->willReturn('Extraction instructions for Ariadne');

        $repoRepo->getRepos('cobb')->willReturn(array(
            'extraction' => $repo
        ));
        $pimple->offsetGet('repo_repository')->willReturn($repoRepo);

        $user->prop('url')->willReturn('/users/cobb');
        $user->prop('username')->willReturn('cobb');
        $user->prop('name')->willReturn('Dominick Cobb');
        $user->loadRepos($repoRepo)->will(function () use ($user, $repo) {
            $user->prop('repos')->willReturn(array(
                'test' => $repo
            ));
        });

        $userRepo->getUser('cobb')->willReturn($user);
        $pimple->offsetGet('user_repository')->willReturn($userRepo);

        $this->beConstructedWith($app, $request);
        $this->setContainer($pimple);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Contentacle\Resources\User');
    }

    function it_should_show_user_details()
    {
        $this->get('cobb')->body->prop('url')->shouldBe('/users/cobb');
        $this->get('cobb')->body->prop('username')->shouldBe('cobb');
        $this->get('cobb')->body->prop('name')->shouldBe('Dominick Cobb');
        $this->get('cobb')->body->prop('repos')->shouldBeArray();
        $this->get('cobb')->body->prop('repos')['test']->prop('url')->shouldBe('/users/cobb/repos/extraction');
        $this->get('cobb')->body->prop('repos')['test']->prop('name')->shouldBe('Extraction 101');
        $this->get('cobb')->body->prop('repos')['test']->prop('description')->shouldBe('Extraction instructions for Ariadne');
    }
}
