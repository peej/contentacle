<?php

namespace spec\Contentacle\Resources;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class RawSpec extends ObjectBehavior
{
    function let(\Tonic\Application $app, \Tonic\Request $request, \Contentacle\Services\RepoRepository $repoRepo, \Contentacle\Models\Repo $repo)
    {
        $repo->document('master', 'new-york/the-hotel/totem.txt')->willReturn(array('content' => 'totem'));
        $repo->document(Argument::cetera())->willThrow(new \Contentacle\Exceptions\RepoException);
        
        $repoRepo->getRepo('cobb', 'extraction')->willReturn($repo);

        
        $this->beConstructedWith(array(
            'app' => $app,
            'request' => $request,
            'repoRepository' => $repoRepo
        ));
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Contentacle\Resources\Raw');
    }

    function it_should_show_a_documents_raw_content($repo)
    {
        $repo->document('master', 'new-york/the-hotel/totem.txt')->shouldBeCalled();
        $response = $this->get('cobb', 'extraction', 'master', 'new-york/the-hotel/totem.txt');
        $response->body->shouldBe('totem');
    }

    function it_should_error_when_trying_to_show_a_directory($repo)
    {
        $repo->document('master', 'new-york/the-hotel')->shouldBeCalled();
        $this->shouldThrow('\Tonic\NotFoundException')->duringGet('cobb', 'extraction', 'master', 'new-york/the-hotel');
    }

}
