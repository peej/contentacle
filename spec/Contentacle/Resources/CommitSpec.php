<?php

namespace spec\Contentacle\Resources;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class CommitSpec extends ObjectBehavior
{
    function let(\Tonic\Application $app, \Tonic\Request $request, \Contentacle\Services\RepoRepository $repoRepo, \Contentacle\Models\Repo $repo)
    {
        $repo->prop('name')->willReturn('Extraction');
        $repo->prop('username')->willReturn('cobb');
        $repo->commit('master', '123456')->willReturn(array(
            'sha' => '123456',
            'authorname' => 'cobb',
            'files' => array('new-york/the-hotel/totem.txt')
        ));
        $repo->commit(Argument::cetera())->willThrow(new \Git\Exception);
        $repo->documents('master', 'new-york/the-hotel/totem.txt')->willThrow(new \Contentacle\Exceptions\RepoException);
        $repo->document('master', 'new-york/the-hotel/totem.txt')->willReturn(array(
            'path' => 'new-york/the-hotel/totem.txt',
            'filename' => 'totem.txt',
            'content' => 'An elegant solution for keeping track of reality.',
            'username' => 'cobb',
            'sha' => '654321',
            'commit' => '123456'
        ));
        $repo->branches()->willReturn(array(
            'master', 'branch'
        ));

        $repoRepo->getRepo('cobb', 'extraction')->willReturn($repo);
        $repoRepo->getRepo(Argument::cetera())->willThrow(new \Git\Exception);

        $this->beConstructedWith(array(
            'app' => $app,
            'request' => $request,
            'response' => function($code = null, $templateName = null) {
                return new \Contentacle\Response($code, '', null, null);
            },
            'repoRepository' => $repoRepo
        ));
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Contentacle\Resources\Commit');
    }

    function it_should_link_to_itself()
    {
        $response = $this->get('cobb', 'extraction', 'master', '123456');
        $response->data['_links']['self']['href']->shouldBe('/users/cobb/repos/extraction/branches/master/commits/123456');
    }

    function it_should_link_to_its_own_documentation()
    {
        $response = $this->get('cobb', 'extraction', 'master', '123456');
        $response->data['_links']['cont:doc']['href']->shouldBe('/rels/commit');
    }

    function it_should_link_to_a_user()
    {
        $response = $this->get('cobb', 'extraction', 'master', '123456');
        $response->data['_links']['cont:user']['href']->shouldBe('/users/cobb');
    }

    function it_should_output_a_commit($repo)
    {
        $repo->commit('master', '123456')->shouldBeCalled();
        $response = $this->get('cobb', 'extraction', 'master', '123456');
        $response->data['sha']->shouldBe('123456');
    }

    function it_should_link_to_the_documents_it_contains()
    {
        $response = $this->get('cobb', 'extraction', 'master', '123456');
        $response->data['_links']['cont:document']['href']->shouldBe('/users/cobb/repos/extraction/branches/master/documents/new-york/the-hotel/totem.txt');
    }

    function it_should_error_for_invalid_commit(\Contentacle\Models\Repo $repo)
    {
        $this->shouldThrow('\Tonic\NotFoundException')->duringGet('ariadne', 'extraction', 'master', '123456');
        $this->shouldThrow('\Tonic\NotFoundException')->duringGet('cobb', 'inception', 'master', '123456');
        $this->shouldThrow('\Tonic\NotFoundException')->duringGet('cobb', 'extraction', 'branch', '123456');
        $this->shouldThrow('\Tonic\NotFoundException')->duringGet('cobb', 'extraction', 'master', '111111');
    }

}
