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
        $this->iHaveACommitWithMessage("peej", "test", "Commit message", new TableNode(<<<TABLE
            | file               | content      |
            | adir/emptyFile.txt |              |
            | afile.txt          | Some content |
TABLE
        ));
        $this->iHaveABranch("branch", "peej", "test");
        $this->iHaveABranch("unmergable", "peej", "test");
        $this->iSwitchToBranch("master", "peej", "test");
        $this->iHaveACommitWithMessage("peej", "test", "Commit message", new TableNode(<<<TABLE
            | file      | content                  |
            | clash.txt | Clash all over the place |
TABLE
        ));
        $this->iSwitchToBranch("unmergable", "peej", "test");
        $this->iHaveACommitWithMessage("peej", "test", "Commit message", new TableNode(<<<TABLE
            | file      | content         |
            | clash.txt | This will clash |
TABLE
        ));
    }

    /**
     * @AfterScenario
     */
    public function tearDownRepo()
    {
        return new Then('I have an empty data store');
    }

    /**
     * @BeforeScenario
     */
    public function setHeaders()
    {
        $this->getSession()->setRequestHeader('Accept', '*/*');
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
        $session = $this->getSession();
        $response = $session->getPage()->getContent();
        
        $data = json_decode($response, true);
        if ($data == false) {
            $yaml = new Yaml;
            $data = $yaml->decode($response);
        }
        $parts = explode('->', $name);
        foreach ($parts as $part) {
            if (!isset($data[$part])) {
                throw new Exception('Could not find '.$part);
            }
            $data = $data[$part];
        }

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
        $session = $this->getSession();
        $response = $session->getPage()->getContent();
        $data = json_decode($response, true);
        if ($data == false) {
            $yaml = new Yaml;
            $data = $yaml->decode($response);
        }
        $parts = explode('->', $name);
        foreach ($parts as $part) {
            if (!isset($data[$part])) {
                throw new Exception;
            }
            $data = $data[$part];
        }
        if (!is_array($data) || !in_array($value, $data)) {
            throw new Exception;
        }
    }

    /**
     * @Then /^response property "([^"]*)" should not exist$/
     */
    public function responsePropertyShouldNotExist($name)
    {
        $session = $this->getSession();
        $response = $session->getPage()->getContent();
        $data = json_decode($response, true);
        if ($data == false) {
            $yaml = new Yaml;
            $data = $yaml->decode($response);
        }
        if (isset($data[$name])) {
            throw new Exception;
        }
    }

    /**
     * @Given /^I send a GET request on "([^"]*)" with sha (\d+)$/
     */
    public function iSendAGetRequestOnWithSha($url, $shaNumber)
    {
        if (!isset($this->shas[$shaNumber - 1])) {
            throw new Exception('There is no generated sha #'.$shaNumber);
        }
        return new Given('I send a GET request on "'.str_replace('{sha}', $this->shas[$shaNumber - 1], $url).'"');
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
     * @Given /^I have user:$/
     */
    public function iHaveUser(TableNode $userData)
    {
        $data = $userData->getHash()[0];
        $data['password'] = sha1($data['username'].':'.$data['password']);
        mkdir($this->repoDir.'/'.$data['username']);
        file_put_contents($this->repoDir.'/'.$data['username'].'/profile.json', json_encode($data, JSON_PRETTY_PRINT));
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

        $yaml = new Yaml;
        $this->shas[] = $repo->add('contentacle.yaml', $yaml->encode($data), 'Initial commit');
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

}
