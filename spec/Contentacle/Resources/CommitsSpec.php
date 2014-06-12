<?php

namespace spec\Contentacle\Resources;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CommitsSpec extends ObjectBehavior
{
    function let(\Tonic\Application $app, \Tonic\Request $request, \Pimple $pimple, \Contentacle\Services\RepoRepository $repoRepo, \Contentacle\Models\Repo $repo)
    {
        $repo->commits(Argument::cetera())->willThrow(new \Tonic\NotFoundException);
        $repo->hasBranch('master')->willReturn(true);
        $repo->hasBranch(Argument::cetera())->willReturn(false);
        
        $repo->commit('master', '123456')->willReturn(array(
            'sha' => '123456'
        ));
        $repo->commits('master', 0, 24)->willReturn(array(
            array('sha' => '123456')
        ));

        $repo->commit('master', '654321')->willReturn(array(
            'sha' => '654321'
        ));
        $repo->commits('master', 25, 49)->willReturn(array(
            array('sha' => '654321')
        ));
        
        $repoRepo->getRepo('cobb', 'extraction')->willReturn($repo);
        $repoRepo->getRepo(Argument::cetera())->willThrow(new \Git\Exception);
        
        $pimple->offsetGet('repo_repository')->willReturn($repoRepo);

        $this->beConstructedWith($app, $request);
        $this->setContainer($pimple);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Contentacle\Resources\Commits');
    }

    function it_should_link_to_itself()
    {
        $this->get('cobb', 'extraction', 'master')->body['_links']['self']['href']->shouldBe('/users/cobb/repos/extraction/branches/master/commits');
    }

    function it_should_link_to_commit_method() {
        $body = $this->get('cobb', 'extraction', 'master')->body;
        $body['_links']['cont:commit']['method']->shouldBe('post');
        $body['_links']['cont:commit']['content-type']->shouldContain('contentacle/commit+yaml');
        $body['_links']['cont:commit']['content-type']->shouldContain('contentacle/commit+json');
    }

    function it_should_list_commits($repo)
    {
        $repo->commits('master', 0, 24)->shouldBeCalled();

        $body = $this->get('cobb', 'extraction', 'master')->body;
        $body['_embedded']['commits'][0]['sha']->shouldBe('123456');
    }

    function it_should_error_for_invalid_branch($repo)
    {
        $this->shouldThrow('\Tonic\NotFoundException')->duringGet('ariadne', 'extraction', 'master');
        $this->shouldThrow('\Tonic\NotFoundException')->duringGet('cobb', 'inception', 'master');
        $this->shouldThrow('\Tonic\NotFoundException')->duringGet('cobb', 'extraction', 'eames');
    }

    function it_should_list_another_page_of_commits($repo)
    {
        $repo->commits('master', 25, 49)->shouldBeCalled();
        $_GET['page'] = 2;

        $body = $this->get('cobb', 'extraction', 'master')->body;
        $body['_embedded']['commits'][0]['sha']->shouldBe('654321');
    }
}
