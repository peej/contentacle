<?php

namespace spec\Contentacle\Resources;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class RawSpec extends ObjectBehavior
{
    function let(\Tonic\Application $app, \Tonic\Request $request, \Pimple $pimple, \Contentacle\Services\RepoRepository $repoRepo, \Contentacle\Models\Repo $repo)
    {
        $repo->loadDocuments('master', 'totem.txt')->willReturn();
        $repo->prop('document')->willReturn(array('content' => 'totem'));
        
        $repoRepo->getRepo('cobb', 'extraction')->willReturn($repo);
        $pimple->offsetGet('repo_repository')->willReturn($repoRepo);

        $this->beConstructedWith($app, $request);
        $this->setContainer($pimple);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Contentacle\Resources\Raw');
    }

    function it_should_show_a_documents_raw_content($repo)
    {
        $repo->loadDocument('master', 'totem.txt')->shouldBeCalled();
        $response = $this->get('cobb', 'extraction', 'master', 'totem.txt');
        $response->body->shouldBe('totem');
    }

}
