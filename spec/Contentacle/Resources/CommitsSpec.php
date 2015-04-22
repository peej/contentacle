<?php

namespace spec\Contentacle\Resources;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CommitsSpec extends ObjectBehavior
{
    function let(\Tonic\Application $app, \Tonic\Request $request, \Contentacle\Services\RepoRepository $repoRepo, \Contentacle\Models\Repo $repo)
    {
        $repo->prop('name')->willReturn('Extraction');
        $repo->prop('username')->willReturn('cobb');
        $repo->commits(Argument::cetera())->willThrow(new \Tonic\NotFoundException);
        $repo->hasBranch('master')->willReturn(true);
        $repo->hasBranch(Argument::cetera())->willReturn(false);
        $repo->branches()->willReturn(array(
            'master', 'branch'
        ));
        $repo->isHead('master', '123456')->willReturn(true);
        $repo->isHead('master', '654321')->willReturn(false);

        $repo->commit('master', '123456')->willReturn(array(
            'sha' => '123456',
            'username' => 'cobb'
        ));
        $repo->commits('master', 0, 24)->willReturn(array(
            array('sha' => '123456')
        ));

        $repo->commit('master', '654321')->willReturn(array(
            'sha' => '654321',
            'username' => 'cobb'
        ));
        $repo->commits('master', 25, 49)->willReturn(array(
            array('sha' => '654321')
        ));

        $repo->commits(Argument::cetera())->willReturn(array(
            array()
        ));
        
        $repoRepo->getRepo('cobb', 'extraction')->willReturn($repo);
        $repoRepo->getRepo(Argument::cetera())->willThrow(new \Git\Exception);

        $this->beConstructedWith(array(
            'app' => $app,
            'request' => $request,
            'response' => function($code = null, $templateName = null) {
                return new \Contentacle\Response($code, $templateName, null, null);
            },
            'repoRepository' => $repoRepo
        ));
    }

    function letgo()
    {
        unset($_GET['page']);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Contentacle\Resources\Commits');
    }

    function it_should_link_to_itself()
    {
        $response = $this->get('cobb', 'extraction', 'master');
        $response->data['_links']['self']['href']->shouldBe('/users/cobb/repos/extraction/branches/master/commits');
    }

    function it_should_link_to_its_own_documentation()
    {
        $response = $this->get('cobb', 'extraction', 'master');
        $response->data['_links']['cont:doc']['href']->shouldBe('/rels/commits');
    }

    function it_should_list_commits($repo)
    {
        $repo->commits('master', 0, 24)->shouldBeCalled();
        $response = $this->get('cobb', 'extraction', 'master');
        $response->data['_embedded']['cont:commit'][0]['_links']['self']['href']->shouldBe('/users/cobb/repos/extraction/branches/master/commits/123456');
        $response->data['_embedded']['cont:commit'][0]['sha']->shouldBe('123456');
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
        $response = $this->get('cobb', 'extraction', 'master');
        $response->data['_embedded']['cont:commit'][0]['sha']->shouldBe('654321');
    }
}
