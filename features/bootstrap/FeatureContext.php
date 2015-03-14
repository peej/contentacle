<?php

use Behat\Behat\Context\ClosuredContextInterface,
    Behat\Behat\Context\TranslatedContextInterface,
    Behat\Behat\Context\BehatContext,
    Behat\Behat\Context\Step\Given,
    Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;
use Behat\MinkExtension\Context\MinkContext;
use Sanpi\Behatch\Context\BehatchContext;
use Contentacle\Services\Yaml;

/**
 * Features context.
 */
class FeatureContext extends MinkContext
{
    private $repoDir, $branch;
    private $shas = array();

    public function __construct(array $parameters)
    {
        $this->useContext('behatch', new BehatchContext($parameters));
        if (!is_dir(dirname(__FILE__).'/../../repos')) {
            mkdir(dirname(__FILE__).'/../../repos');
        }
        $this->repoDir = realpath(dirname(__FILE__).'/../../repos');
    }

    /**
     * @BeforeScenario
     */
    public function setupRepo()
    {
        $this->iHaveAnEmptyDataStore();
        $this->iHaveUser(new TableNode(<<<TABLE
            | username | password | name       | email           |
            | peej     | test     | Paul James | paul@peej.co.uk |
TABLE
        ));
        $this->iHaveUser(new TableNode(<<<TABLE
            | username | password | name       | email |
            | empty    | test     | Empty user |       |
TABLE
        ));
        $this->iHaveARepo(new TableNode(<<<TABLE
            | username | name | title | description    |
            | peej     | test | Test  | No description |
TABLE
        ));
        $this->iHaveARepo(new TableNode(<<<TABLE
            | username | name  | title | description |
            | peej     | empty | Empty |             |
TABLE
        ));
        $this->iHaveACommitWithMessage("peej", "test", "1st commit", new TableNode(<<<TABLE
            | file               | content      |
            | adir/emptyFile.txt |              |
            | afile.txt          | Some content |
            | anotherFile.txt    | More         |
TABLE
        ));
        $this->iHaveABranch("branch", "peej", "test");
        $this->iHaveABranch("unmergable", "peej", "test");
        $this->iSwitchToBranch("master", "peej", "test");
        $this->iHaveACommitWithMessage("peej", "test", "2nd commit", new TableNode(<<<TABLE
            | file      | content                  |
            | clash.txt | Clash all over the place |
TABLE
        ));
        $this->iSwitchToBranch("unmergable", "peej", "test");
        $this->iHaveACommitWithMessage("peej", "test", "3rd commit", new TableNode(<<<TABLE
            | file      | content         |
            | clash.txt | This will clash |
TABLE
        ));
        $this->iSwitchToBranch("master", "peej", "test");
        $this->iResetTheIndex("peej", "test");
        $this->iHaveACommitWithMessage("peej", "test", "4th commit message is going to be a longer one for testing longer commit messages.", new TableNode(<<<TABLE
            | file                      | content                 |
            | adir/and/another/file.txt | Deeply nested directory |
TABLE
        ));
        $this->iHaveACommitedFile("example.md", "peej", "test", "Adding some Markdown.");
    }

    /**
     * @sAfterScenario
     */
    public function tearDownRepo()
    {
        $this->iHaveAnEmptyDataStore();
    }

    /**
     * @BeforeScenario
     */
    public function setHeaders()
    {
        $this->getSession()->setRequestHeader('Accept', '*/*');
    }

    private function getResponseBody()
    {
        $session = $this->getSession();
        $response = $session->getPage()->getContent();
        
        $data = json_decode($response, true);
        if ($data == false) {
            $yaml = new Yaml;
            $data = $yaml->decode($response);
        }

        return $data;
    }

    private function getResponseProperty($name)
    {
        $data = $this->getResponseBody();
        $parts = explode('->', $name);
        foreach ($parts as $part) {
            if (!array_key_exists($part, $data)) {
                throw new Exception('Could not find '.$part);
            }
            $data = $data[$part];
        }
        return $data;
    }

    /**
     * Set HTTP header for next request
     *
     * @When /^I set the "(?P<header>[^"]*)" header to "(?P<value>[^"]*)"$/
     */
    public function iSetRequestHeaderAs($header, $value)
    {
        $this->getSession()->setRequestHeader($header, $value);
    }

    /**
     * @Then /^the "([^"]*)" response header should be "([^"]*)"$/
     */
    public function theResponseHeaderShouldBe($header, $value)
    {
        $headers = $this->getSession()->getResponseHeaders();
        if (!isset($headers[$header]) || $headers[$header] != array($value)) {
            throw new Exception;
        }
    }

    /**
     * @Then /^the response should have the property "([^"]*)" with the value "([^"]*)"$/
     * @Then /^response property "([^"]*)" should be "([^"]*)"$/
     */
    public function theResponseShouldHavePropertyWithValue($name, $value)
    {
        $data = $this->getResponseProperty($name);

        if ($data === false) {
            $data = 'false';
        } elseif ($data === true) {
            $data = 'true';
        }

        if ($data !== $value) {
            throw new Exception($name.' is "'.$data.'" not "'.$value.'"');
        }
    }

    /**
     * @Given /^response property "([^"]*)" should be sha (\d+)$/
     */
    public function responsePropertyShouldBeSha($name, $shaNumber)
    {
        if (!isset($this->shas[$shaNumber - 1])) {
            throw new Exception('There is no generated sha #'.$shaNumber);
        }
        return new Given('response property "'.$name.'" should be "'.$this->shas[$shaNumber - 1].'"');
    }

    /**
     * @Given /^response property "([^"]*)" should be "([^"]*)" with sha (\d+)$/
     */
    public function responsePropertyShouldBeWithSha($name, $value, $shaNumber)
    {
        if (!isset($this->shas[$shaNumber - 1])) {
            throw new Exception('There is no generated sha #'.$shaNumber);
        }
        return new Given('response property "'.$name.'" should be "'.str_replace('{sha}', $this->shas[$shaNumber - 1], $value).'"');
    }

    /**
     * @Given /^response property "([^"]*)" should contain "([^"]*)"$/
     */
    public function responsePropertyShouldContain($name, $value)
    {
        $data = $this->getResponseProperty($name);

        if (!is_array($data) || !in_array($value, $data)) {
            throw new Exception;
        }
    }

    /**
     * @Then /^response property "([^"]*)" should exist$/
     */
    public function responsePropertyShouldExist($name)
    {
        $this->getResponseProperty($name);
    }

    /**
     * @Then /^response property "([^"]*)" should not exist$/
     */
    public function responsePropertyShouldNotExist($name)
    {
        try {
            $this->getResponseProperty($name);
        } catch (Exception $e) {
            return;
        }
        throw new Exception('Property '.$name.' exists');
    }

    /**
     * @Given /^I send an OPTIONS request (?:on|to) "([^"]*)"$/
     */
    public function iSendAnOptionsRequestTo($url)
    {
        return new Given('I send a OPTIONS request to "'.$url.'"');
    }

    /**
     * @Given /^I send an? ([A-Z]+) request (?:on|to) "([^"]*)" with sha (\d+)$/
     */
    public function iSendAGetRequestOnWithSha($method, $url, $shaNumber)
    {
        if (!isset($this->shas[$shaNumber - 1])) {
            throw new Exception('There is no generated sha #'.$shaNumber);
        }
        return new Given('I send a '.$method.' request on "'.str_replace('{sha}', $this->shas[$shaNumber - 1], $url).'"');
    }

    /**
     * @Given /^I send an? ([A-Z]+) request (?:on|to) "([^"]*)" with sha (\d+) and body:$/
     */
    public function iSendAPostRequestOnWithShaAndBody($method, $url, $shaNumber, PyStringNode $body)
    {
        if (!isset($this->shas[$shaNumber - 1])) {
            throw new Exception('There is no generated sha #'.$shaNumber);
        }
        return new Given('I send a '.$method.' request on "'.str_replace('{sha}', $this->shas[$shaNumber - 1], $url).'" with body:', $body);
    }

    /**
     * @Given /^the directory "([^"]*)" should exist$/
     */
    public function theDirectoryShouldExist($filename)
    {
        if (!is_dir(realpath(dirname(__FILE__).'/../../repos/'.$filename))) {
            throw new Exception;
        }
    }

    /**
     * @Given /^the directory "([^"]*)" should not exist$/
     */
    public function theDirectoryShouldNotExist($filename)
    {
        if (is_dir(realpath(dirname(__FILE__).'/../../repos/'.$filename))) {
            throw new Exception;
        }
    }

    /**
     * @Given /^I have an empty data store$/
     */
    public function iHaveAnEmptyDataStore()
    {
        exec('rm -rf '.$this->repoDir);
        @mkdir($this->repoDir);
    }

    /**
     * @Given /^I have a user:$/
     * @Given /^I have users:$/
     */
    public function iHaveUser(TableNode $userData)
    {
        foreach ($userData->getHash() as $data) {
            if (function_exists('password_hash')) {
                $data['password'] = password_hash($data['username'].':'.$data['password'], PASSWORD_DEFAULT);
            } else {
                $data['password'] = sha1($data['username'].':'.$data['password']);
            }
            mkdir($this->repoDir.'/'.$data['username']);
            file_put_contents($this->repoDir.'/'.$data['username'].'/profile.json', json_encode($data, JSON_PRETTY_PRINT));
        }
    }

    private function getRepo($username, $repoName)
    {
        $userData = json_decode(file_get_contents($this->repoDir.'/'.$username.'/profile.json'), true);
        $repo = new Git\Repo($this->repoDir.'/'.$username.'/'.$repoName.'.git');
        $repo->setUser($userData['name'], $userData['email']);
        if (isset($this->branch[$username.'/'.$repoName])) {
            $repo->setBranch($this->branch[$username.'/'.$repoName]);
        }
        return $repo;
    }

    /**
     * @Given /^I have a repo:$/
     */
    public function iHaveARepo(TableNode $repoData)
    {
        $data = $repoData->getHash()[0];
        $repo = $this->getRepo($data['username'], $data['name']);
        $repoPath = $this->repoDir.'/'.$data['username'].'/'.$data['name'].'.git/description';

        file_put_contents($repoPath, $data['description']."\n");
    }

    /**
     * @Given /^I have a commit in "([^"\/]*)\/([^"]*)" with message "([^"]*)":$/
     */
    public function iHaveACommitWithMessage($username, $repoName, $message, TableNode $commitData)
    {
        $data = $commitData->getHash();

        $repo = $this->getRepo($username, $repoName);

        foreach ($data as $item) {
            $repo->add($item['file'], $item['content']);
        }

        $this->shas[] = $repo->save($message);
    }

    /**
     * @Given /^I have a commited file "([^"]*)" in "([^"\/]*)\/([^"]*)" with message "([^"]*)"$/
     */
    public function iHaveACommitedFile($filename, $username, $repoName, $message)
    {
        $repo = $this->getRepo($username, $repoName);

        $repo->add($filename, file_get_contents(dirname(__FILE__).'/'.$filename));

        $this->shas[] = $repo->save($message);
    }

    /**
     * @Given /^I have a branch "([^"]*)" in "([^"\/]*)\/([^"]*)"$/
     */
    public function iHaveABranch($branchName, $username, $repoName)
    {
        $repo = $this->getRepo($username, $repoName);
        $repo->createBranch($branchName);
    }

    /**
     * @Given /^I switch to branch "([^"]*)" in "([^"\/]*)\/([^"]*)"$/
     */
    public function iSwitchToBranch($branchName, $username, $repoName)
    {
        $this->branch[$username.'/'.$repoName] = $branchName;
    }

    /**
     * @Given /^I reset the index of "([^"\/]*)\/([^"]*)"$/
     */
    public function iResetTheIndex($username, $repoName)
    {
        $repo = $this->getRepo($username, $repoName);
        $repo->setBranch($this->branch[$username.'/'.$repoName]);
        $repo->resetIndex();
    }

    /**
     * @Given /^I remember the commit sha from the location header$/
     */
    public function iRememberTheCommitShaFromTheLocationHeader()
    {
        $session = $this->getSession();
        $responseHeaders = $session->getResponseHeaders();
        if (!isset($responseHeaders['Location'])) {
            throw new Exception('No location header returned');
        }
        $this->shas[] = substr($responseHeaders['Location'][0], -40);
    }

    /**
     * @Given /^I follow the (?:(\d+)[a-z]{2} )?"([^"]*)" relation$/
     */
    public function iFollowTheRelation($num, $rel)
    {
        if (!$num) $num = 1;

        $data = $this->getResponseBody();

        if (!isset($data['_links']) && !isset($data['_embedded'])) {
            throw new Exception('No relations in the response');
        }

        if (isset($data['_links'][$rel])) {
            if (!isset($data['_links'][$rel]['href'])) {
                $href = $data['_links'][$rel][$num - 1]['href'];
            } else {
                $href = $data['_links'][$rel]['href'];
            }

        } elseif (isset($data['_embedded'][$rel])) {
            $items = $data['_embedded'][$rel];
            if (!isset($items[$num - 1])) {
                throw new Exception('Relation "'.$rel.'" does not have '.$num.' items');
            }
            $href = $items[$num - 1]['_links']['self']['href'];

        } else {
            throw new Exception('Relation "'.$rel.'" not found');
        }

        return new Given('I send a GET request to "'.$href.'"');
    }

    /**
     * @When /^I uncurie the "([^"]*)" relation$/
     */
    public function iUncurieTheRelation($rel)
    {
        $data = $this->getResponseBody();
        list($curieName, $relName) = explode(':', $rel);

        if (!isset($data['_links']['curies'])) {
            throw new Exception('No curies found in document');
        }

        foreach ($data['_links']['curies'] as $curie) {
            if ($curie['name'] == $curieName) {
                $href = str_replace('{rel}', $relName, $curie['href']);
            }
        }

        if (!isset($href)) {
            throw new Exception('Curie "'.$curieName.'" not found');
        }

        return new Given('I send a GET request to "'.$href.'"');
    }

    /**
     * @Then /^I should see a link with relation "([^"]*)" to "([^"]*)"$/
     */
    public function iShouldSeeALinkWithRelationTo($rel, $href)
    {
        $session = $this->getSession();
        $page = $session->getPage();
        
        $selector = 'link[rel=\''.$rel.'\'][href=\''.$href.'\']';

        if (!$page->find('css', $selector)) {
            throw new Exception;
        }
    }

    /**
     * @Then /^I should see a link to "([^"]*)"$/
     */
    public function iShouldSeeALinkTo($url)
    {
        return new Given('I should see an "a[href=\''.$url.'\']" element');
    }

    /**
     * @Given /^print sha (\d+)$/
     */
    public function printSha($shaNumber)
    {
        echo $this->shas[$shaNumber - 1], "\n";
    }

}
