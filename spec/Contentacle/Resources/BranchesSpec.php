<?php

namespace spec\Contentacle\Resources;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class BranchesSpec extends ObjectBehavior
{
    function let(\Tonic\Application $app, \Contentacle\Request $request, \Contentacle\Services\RepoRepository $repoRepo, \Contentacle\Models\Repo $repo)
    {
        $repo->prop(Argument::any())->willReturn();
        $repo->branches()->willReturn(array('master', 'branch'));
        $repo->hasBranch('master')->willReturn(true);
        $repo->hasBranch('branch')->willReturn(true);
        $repo->commits(Argument::cetera())->willReturn(array(
            array()
        ));
        $repo->parentRepo()->willReturn(null);

        $repoRepo->getRepo('cobb', 'extraction')->willReturn($repo);
        $repoRepo->getRepo(Argument::cetera())->willThrow(new \Contentacle\Exceptions\RepoException);

        $this->beConstructedWith(array(
            'app' => $app,
            'request' => $request,
            'response' => function($code = null, $templateName = null) {
                return new \Contentacle\Response($code, $templateName, null, null);
            },
            'repoRepository' => $repoRepo
        ));
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Contentacle\Resources\Branches');
    }

    function it_should_link_to_itself()
    {
        $response = $this->get('cobb', 'extraction');
        $response->data['_links']['self']['href']->shouldBe('/users/cobb/repos/extraction/branches');
    }

    function it_should_link_to_its_own_documentation()
    {
        $response = $this->get('cobb', 'extraction');
        $response->data['_links']['cont:doc']['href']->shouldBe('/rels/branches');
    }

    function it_should_embed_branches()
    {
        $response = $this->get('cobb', 'extraction');
        $response->data['_embedded']['cont:branch']->shouldHaveCount(2);
        $response->data['_embedded']['cont:branch'][0]['name']->shouldBe('master');
        $response->data['_embedded']['cont:branch'][1]['name']->shouldBe('branch');
    }

    function it_should_error_for_unknown_repo()
    {
        $this->shouldThrow('\Tonic\NotFoundException')->duringGet('ariadne', 'extraction');
        $this->shouldThrow('\Tonic\NotFoundException')->duringGet('cobb', 'inception');
    }
}
