<?php

namespace spec\Contentacle\Resources;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class DocumentsSpec extends ObjectBehavior
{
    function let(\Tonic\Application $app, \Tonic\Request $request, \Pimple $pimple, \Contentacle\Services\RepoRepository $repoRepo, \Contentacle\Models\Repo $repo)
    {
        $repo->loadDocuments('master', null)->willReturn();
        $repo->loadDocuments('master', '/woo/yay')->willReturn();
        $repo->loadDocuments(Argument::cetera())->willThrow(new \Tonic\NotFoundException);
        $repo->prop('documents')->willReturn(array());
        $repo->prop('document')->willReturn(array());
        
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
        $repo->loadDocuments('master', null)->shouldBeCalled();
        $repo->prop('documents')->willReturn('documents');
        $response = $this->get('cobb', 'extraction', 'master');
        $response->body->shouldBe('documents');
    }

    function it_should_show_document_listing_within_a_subdirectory($repo)
    {
        $repo->loadDocuments('master', '/woo/yay')->shouldBeCalled();
        $repo->prop('documents')->willReturn('documents');
        $response = $this->get('cobb', 'extraction', 'master', '/woo/yay');
        $response->body->shouldBe('documents');
    }

    function it_should_show_a_single_document($repo)
    {
        $repo->loadDocuments('master', null)->shouldBeCalled();
        $repo->prop('document')->willReturn('document');
        $response = $this->get('cobb', 'extraction', 'master');
        $response->body->shouldBe('document');
    }

    function it_should_error_for_unknown_branch()
    {
        $this->shouldThrow('\Tonic\NotFoundException')->duringGet('cobb', 'extraction', 'eames');
    }
}
