<?php

namespace spec\Contentacle\Resources;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class UserSpec extends ObjectBehavior
{
    function let(\Tonic\Application $app, \Tonic\Request $request, \Pimple $pimple, \Contentacle\Services\UserRepository $userRepo, \Contentacle\Services\RepoRepository $repoRepo)
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

        $user = (object)array(
            'username' => 'cobb',
            'name' => 'Dominick Cobb'
        );
        
        $userRepo->getUser('cobb')->willReturn($user);
        $userRepo->getUser(Argument::cetera())->willThrow(new \Contentacle\Exceptions\UserException);
        
        $pimple->offsetGet('repo_repository')->willReturn($repoRepo);
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
}
