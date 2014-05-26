<?php

namespace spec\Contentacle\Resources;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class DocumentsSpec extends ObjectBehavior
{
    function let(\Tonic\Application $app, \Tonic\Request $request, \Pimple $pimple, \Contentacle\Services\RepoRepository $repoRepo, \Contentacle\Models\Repo $repo)
    {
        $repo->documents('master', null)->willReturn(array());
        $repo->documents('master', 'new-york/the-hotel')->willReturn(array());
        $repo->documents('master', 'totem.txt')->willReturn(array());
        $repo->documents(Argument::cetera())->willThrow(new \Tonic\NotFoundException);
        
        $repoRepo->getRepo('cobb', 'extraction')->willReturn($repo);
        $pimple->offsetGet('repo_repository')->willReturn($repoRepo);

        $this->beConstructedWith($app, $request);
        $this->setContainer($pimple);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Contentacle\Resources\Documents');
    }

    function it_should_show_document_listing($repo)
    {
        $repo->documents('master', null)->willReturn('documents')->shouldBeCalled();
        $response = $this->get('cobb', 'extraction', 'master');
        $response->body->shouldBe('documents');
    }

    function it_should_show_document_listing_within_a_subdirectory($repo)
    {
        $repo->documents('master', 'new-york/the-hotel')->willReturn('documents')->shouldBeCalled();
        $response = $this->get('cobb', 'extraction', 'master', 'new-york/the-hotel');
        $response->body->shouldBe('documents');
    }

    function it_should_show_a_single_document($repo)
    {
        $repo->documents('master', 'totem.txt')->willReturn('document')->shouldBeCalled();
        $response = $this->get('cobb', 'extraction', 'master', 'totem.txt');
        $response->body->shouldBe('document');
    }

    function it_should_error_for_unknown_branch()
    {
        $this->shouldThrow('\Tonic\NotFoundException')->duringGet('cobb', 'extraction', 'eames');
    }
}
