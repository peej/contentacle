<?php

namespace spec\Contentacle\Resources;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class ReposSpec extends ObjectBehavior
{
    function let(\Tonic\Application $app, \Tonic\Request $request, \Pimple $pimple, \Contentacle\Services\RepoRepository $repoRepo, \Contentacle\Services\UserRepository $userRepo)
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

        $repo3 = (object)array(
            'name' => 'limbo',
            'username' => 'cobb',
            'title' => 'Limbo',
            'description' => 'Just raw, infinite subconscious.'
        );

        $user = (object)array(
            'username' => 'cobb',
            'name' => 'Dominick Cobb'
        );

        $repoRepo->getRepos('cobb', null)->willReturn(array(
            'extraction' => $repo1,
            'inception' => $repo2
        ));
        $repoRepo->getRepos(Argument::cetera())->willThrow(new \Contentacle\Exceptions\RepoException);

        $repoRepo->getRepo('cobb', 'extraction')->willReturn($repo1);
        $repoRepo->getRepo('cobb', 'inception')->willReturn($repo2);
        $repoRepo->getRepo(Argument::cetera())->willThrow(new \Git\Exception);
        
        $repoRepo->createRepo($user, array(
            'name' => 'limbo',
            'title' => 'Limbo',
            'description' => 'Just raw, infinite subconscious.'
        ))->willReturn($repo3);

        $exception = new \Contentacle\Exceptions\ValidationException;
        $exception->errors = array('name', 'title');
        $repoRepo->createRepo($user, array(
            'name' => '***'
        ))->willThrow($exception);

        $userRepo->getUser('cobb')->willReturn($user);

        $pimple->offsetGet('repo_repository')->willReturn($repoRepo);
        $pimple->offsetGet('user_repository')->willReturn($userRepo);

        $this->beConstructedWith($app, $request);
        $this->setContainer($pimple);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Contentacle\Resources\Repos');
    }

    function it_should_link_to_itself()
    {
        $this->get('cobb')->body['_links']['self']['href']->shouldBe('/users/cobb/repos');
    }

    function it_should_link_to_add_method() {
        $body = $this->get('cobb')->body;
        $body['_links']['cont:create-repo']['method']->shouldBe('post');
        $body['_links']['cont:create-repo']['content-type']->shouldContain('contentacle/repo+yaml');
        $body['_links']['cont:create-repo']['content-type']->shouldContain('contentacle/repo+json');
    }

    function it_should_get_a_list_of_a_users_repos()
    {
        $body = $this->get('cobb')->body;
        $body['_links']['self']['href']->shouldBe('/users/cobb/repos');
        $body['_embedded']['repos'][0]['_links']['self']['href']->shouldBe('/users/cobb/repos/extraction');
        $body['_embedded']['repos'][0]['_links']['cont:branches']['href']->shouldBe('/users/cobb/repos/extraction/branches');
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

    function it_should_create_a_repo($request)
    {
        $request->getData()->willReturn(array(
            'name' => 'limbo',
            'title' => 'Limbo',
            'description' => 'Just raw, infinite subconscious.'
        ));

        $response = $this->createRepo('cobb');

        $response->code->shouldBe(201);
        $response->location->shouldBe('/users/cobb/repos/limbo');
    }

    function it_should_fail_to_create_a_bad_repo($request)
    {
        $request->getData()->willReturn(array(
            'name' => '***'
        ));

        $response = $this->createRepo('cobb');

        $response->code->shouldBe(400);
        $response->contentType->shouldBe('application/hal');
        $response->body['_embedded']['errors'][0]['logref']->shouldBe('name');
        $response->body['_embedded']['errors'][1]['logref']->shouldBe('title');
    }
}
