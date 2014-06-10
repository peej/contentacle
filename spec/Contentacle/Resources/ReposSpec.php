<?php

namespace spec\Contentacle\Resources;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ReposSpec extends ObjectBehavior
{
    function let(\Tonic\Application $app, \Tonic\Request $request, \Pimple $pimple, \Contentacle\Services\RepoRepository $repoRepo)
    {
        $repo1 = (object)array(
            'name' => 'extraction',
            'username' => 'cobb',
            'title' => 'Extraction 101',
            'description' => 'Extraction instructions for Ariadne'
        );

        $repo2 = (object)array(
            'name' => 'inception',
            'username' => 'cobb',
            'title' => 'Inception',
            'description' => 'Notes on the concept of inception for Eames'
        );

        $repoRepo->getRepos('cobb')->willReturn(array(
            'extraction' => $repo1,
            'inception' => $repo2
        ));
        $repoRepo->getRepos(Argument::cetera())->willThrow(new \Contentacle\Exceptions\RepoException);

        $repoRepo->getRepo('cobb', 'extraction')->willReturn($repo1);
        $repoRepo->getRepo('cobb', 'inception')->willReturn($repo2);
        $repoRepo->getRepo(Argument::cetera())->willThrow(new \Git\Exception);

        $pimple->offsetGet('repo_repository')->willReturn($repoRepo);

        $this->beConstructedWith($app, $request);
        $this->setContainer($pimple);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Contentacle\Resources\Repos');
    }

    function it_should_get_a_list_of_a_users_repos()
    {
        $body = $this->get('cobb')->body;
        $body['_links']['self']['href']->shouldBe('/users/cobb/repos');
        $body['_embedded']['repos'][0]['_links']['self']['href']->shouldBe('/users/cobb/repos/extraction');
        $body['_embedded']['repos'][0]['_links']['branches']['href']->shouldBe('/users/cobb/repos/extraction/branches');
        $body['_embedded']['repos'][0]['name']->shouldBe('extraction');
        $body['_embedded']['repos'][0]['title']->shouldBe('Extraction 101');
        $body['_embedded']['repos']->shouldHaveCount(2);
        $body['_embedded']['repos'][1]['_links']['self']['href']->shouldBe('/users/cobb/repos/inception');
        $body['_embedded']['repos'][1]['name']->shouldBe('inception');
        $body['_embedded']['repos'][1]['title']->shouldBe('Inception');
    }

    function it_should_error_for_unknown_user()
    {
        $this->shouldThrow('\Tonic\NotFoundException')->duringGet('ariadne');
    }
}
