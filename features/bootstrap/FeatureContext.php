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
     * @Then /^the response should have the property "([^"]*)" with the value "([^"]*)"$/
     * @Then /^response property "([^"]*)" should be "([^"]*)"$/
     */
    public function theResponseShouldHavePropertyWithValue($name, $value)
    {
        $response = $this->getSession()->getPage()->getContent();
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

}
