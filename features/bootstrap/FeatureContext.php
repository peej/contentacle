<?php

use Behat\Behat\Context\ClosuredContextInterface,
    Behat\Behat\Context\TranslatedContextInterface,
    Behat\Behat\Context\BehatContext,
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
    public function __construct(array $parameters)
    {
        $this->useContext('behatch', new BehatchContext($parameters));
    }

    /**
     * @BeforeScenario
     */
    public function copyTestRepo()
    {
        $from = realpath(dirname(__FILE__).'/../repos/peej');
        $to = realpath(dirname(__FILE__).'/../../repos').'/';
        exec("rm -rf $to*");
        exec("cp -r $from $to 2> /dev/null");
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
                throw new Exception;
            }
            $data = $data[$part];
        }
        if ($data != $value) {
            throw new Exception;
        }
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

}
