<?php

namespace spec\Contentacle\Models;

use PhpSpec\ObjectBehavior;
use Prophecy\Argument;

class RepoSpec extends ObjectBehavior
{
    function let(
        \Contentacle\Services\UserRepository $userRepo,
        \Contentacle\Models\User $user,
        \Git\Repo $repo,
        \Git\Tree $rootTree,
        \Git\Tree $subTree,
        \Git\Blob $totem,
        \Git\Commit $commit,
        \Contentacle\Services\FileAccess $fileAccess,
        \Contentacle\Services\Yaml $yaml,
        \Contentacle\Services\Diff $diff
    )
    {
        $fileAccess->read(Argument::any())->willReturn('Extraction instructions for Ariadne');
        
        $repo->setUser(Argument::any(), Argument::any())->willReturn(true);
        $repo->getBranches()->willReturn(array('master', 'arthur'));
        $repo->setBranch('master')->willReturn();
        
        $rootTree->entries()->willReturn(array(
            'totem.txt' => (object)array('filename' => 'totem.txt')
        ));
        $repo->tree('')->willReturn($rootTree);

        $subTree->entries()->willReturn(array(
            'mr-charles.txt' => (object)array('filename' => 'mr-charles.txt')
        ));
        $repo->tree('new-york/the-hotel')->willReturn($subTree);

        $totem->getContent()->willReturn('A Totem is an object that is used to test if oneself is in one\'s own reality and not in another person\'s dream.');
        $repo->file('totem.txt')->willReturn($totem);
        $repo->tree('totem.txt')->willReturn(null);
        $repo->log('totem.txt')->willReturn(array('123456'));

        $commit->sha = '123456';
        $commit->getMetadata('parents')->willReturn(array('654321'));
        $commit->getMetadata('message')->willReturn('Added information about forever spinning totems');
        $commit->getMetadata('date')->willReturn('1392493822');
        $commit->getMetadata('user')->willReturn('Dominick Cobb');
        $commit->getMetadata('email')->willReturn('cobb@localhost');
        $commit->getFiles()->willReturn(array('totem.txt', 'new-york/the-hotel/mr-charles.txt'));
        $commit->getMetadata('diff')->willReturn((object)array(
            'diff' => array(
                'totem.txt' => array(
                    '1+A Totem is an object that is used to test if oneself is in one\'s own reality and not in another person\'s dream.'
                )
            )
        ));
        
        $repo->commits(null, 25)->willReturn(array($commit));
        $repo->commit('123456')->willReturn($commit);
        
        $totem->getHistory()->willReturn(array($commit));

        $userRepo->getUser('cobb')->willReturn($user);
        $userRepo->getUsernameFromEmail('cobb@localhost')->willReturn('cobb');

        $data = array(
            'username' => 'cobb',
            'name' => 'extraction',
            'path' => 'extraction'
        );
        $gitProvider = function ($username, $repoName) use ($repo) {
            return $repo->getWrappedObject();
        };
        $this->beConstructedWith($data, $gitProvider, '', $userRepo, $fileAccess, $yaml, $diff);
    }

    function it_is_initializable()
    {
        $this->shouldHaveType('Contentacle\Models\Repo');
    }

    function it_should_have_properties()
    {
        $this->username->shouldBe('cobb');
        $this->name->shouldBe('extraction');
        $this->description->shouldBe('Extraction instructions for Ariadne');
    }

    function it_should_load_branch_data_from_the_repo()
    {
        $this->branches()->shouldBe(array(
            'master',
            'arthur'
        ));
    }

    function it_should_be_able_to_check_if_a_branch_exists()
    {
        $this->hasBranch('arthur')->shouldBe(true);
        $this->hasBranch('eames')->shouldBe(false);
    }

    function it_should_get_document_metadata()
    {
        $documents = $this->documents();
        $documents->shouldBe(array(
            'totem.txt' => 'totem.txt'
        ));
    }

    function it_should_get_document_metadata_from_within_a_subdirectory()
    {
        $documents = $this->documents('master', 'new-york/the-hotel');
        $documents->shouldBe(array(
            'mr-charles.txt' => 'mr-charles.txt'
        ));
    }

    function it_should_get_a_documents_metadata()
    {
        $document = $this->document('master', 'totem.txt');
        $document['content']->shouldBe('A Totem is an object that is used to test if oneself is in one\'s own reality and not in another person\'s dream.');
    }

    function it_should_get_a_documents_history()
    {
        $history = $this->history('master', 'totem.txt');
        $history[0]['sha']->shouldBe('123456');
        $history[0]['message']->shouldBe('Added information about forever spinning totems');
        $history[0]['date']->shouldBe('1392493822');
        $history[0]['author']->shouldBe('Dominick Cobb');
        $history[0]['authorname']->shouldBe('cobb');
        $history[0]['email']->shouldBe('cobb@localhost');
    }

    function it_should_get_commits()
    {
        $commits = $this->commits('master');
        $commits[0]['sha']->shouldBe('123456');
        $commits[0]['message']->shouldBe('Added information about forever spinning totems');
        $commits[0]['date']->shouldBe('1392493822');
        $commits[0]['author']->shouldBe('Dominick Cobb');
        $commits[0]['authorname']->shouldBe('cobb');
        $commits[0]['email']->shouldBe('cobb@localhost');
    }

    function it_should_get_a_commit()
    {
        $commit = $this->commit('master', '123456');
        $commit['sha']->shouldBe('123456');
        $commit['parents']->shouldBe(array('654321'));
        $commit['message']->shouldBe('Added information about forever spinning totems');
        $commit['date']->shouldBe('1392493822');
        $commit['author']->shouldBe('Dominick Cobb');
        $commit['authorname']->shouldBe('cobb');
        $commit['email']->shouldBe('cobb@localhost');
        $commit['files']->shouldBe(array(
            'totem.txt', 'new-york/the-hotel/mr-charles.txt'
        ));
    }

    function it_should_save_a_new_file($repo)
    {
        $repo->add('test', 'test', 'Commit message')->willReturn('add');
        $repo->file('test')->willThrow('\Git\Exception');
        $this->createDocument('master', 'test', 'test', 'Commit message');
    }

    function it_should_save_an_existing_file($repo)
    {
        $repo->update('test', 'test', 'Commit message')->willReturn('update');
        $repo->file('test')->willReturn();
        $this->updateDocument('master', 'test', 'test', 'Commit message');
    }
}
