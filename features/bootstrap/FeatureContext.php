<?php

use Behat\Behat\Context\ClosuredContextInterface,
    Behat\Behat\Context\TranslatedContextInterface,
    Behat\Behat\Context\BehatContext,
    Behat\Behat\Exception\PendingException;
use Behat\Gherkin\Node\PyStringNode,
    Behat\Gherkin\Node\TableNode;
use Behat\MinkExtension\Context\MinkContext;
use Behat\Behat\Context\Step\Given,
    Behat\Behat\Context\Step\When,
    Behat\Behat\Context\Step\Then;

//
// Require 3rd-party libraries here:
//
//   require_once 'PHPUnit/Autoload.php';
//   require_once 'PHPUnit/Framework/Assert/Functions.php';
//

/**
 * Features context.
 */
class FeatureContext extends MinkContext
{
    private $repoDir;

    /**
     * Initializes context.
     * Every scenario gets its own context object.
     *
     * @param array $parameters context parameters (set them up through behat.yml)
     */
    public function __construct(array $parameters)
    {
        $this->repoDir = __DIR__.'/../../repos';
    }

    /**
     * @BeforeScenario
     */
    public function removeUsers()
    {
        if (is_dir($this->repoDir.'/testuser')) {
            $this->delTree($this->repoDir.'/testuser');
        }
    }

    private function delTree($dir) {
        $files = array_diff(scandir($dir), array('.','..'));
        foreach ($files as $file) {
            (is_dir("$dir/$file")) ? $this->delTree("$dir/$file") : unlink("$dir/$file");
        }
        return rmdir($dir);
    }

    /**
     * @Given /^I should see a "([^"]*)" button$/
     */
    public function iShouldSeeAButton($text)
    {
        return new Given('I should see "'.$text.'" in the ".button" element');
    }

    /**
     * @Given /^a user:$/
     */
    public function aUser(TableNode $table)
    {
        foreach ($table->getHash() as $row) {
            @mkdir($this->repoDir.'/'.$row['username']);
            file_put_contents($this->repoDir.'/'.$row['username'].'/profile.json', json_encode(array(
                'name' => $row['name'],
                'email' => $row['username'].'@localhost',
                'password' => sha1($row['password'].\Contentacle\Models\User::PASSWORD_SALT)
            )));
            $emailFilename = $this->repoDir.'/emails.json';
            $emails = json_decode(file_get_contents($emailFilename), true);
            $emails[$row['username'].'@localhost'] = $row['username'];
            file_put_contents($emailFilename, json_encode($emails, true));
        }
        $this->aRepo($table);
    }

    /**
     * @Given /^a repo:$/
     */
    public function aRepo(TableNode $table)
    {
        foreach ($table->getHash() as $row) {
            if (isset($row['username']) && isset($row['repo']) && is_dir($this->repoDir.'/'.$row['username'])) {
                if (!isset($row['email'])) {
                    $row['email'] = $row['username'].'@localhost';
                }
                $repo = new Git\Repo($this->repoDir.'/'.$row['username'].'/'.$row['repo'].'.git');
                $repo->setUser($row['username'], $row['email']);
                $repo->add('testfile.txt', 'this is a test');
                $repo->save('Test commit');
            }
        }
    }

    /**
     * @Given /^a repo "([^"]*)" for user "([^"]*)" with commit "([^"]*)":$/
     */
    public function aRepoForUserWithCommit($repoName, $username, $commitMessage, TableNode $table)
    {
        if (is_dir($this->repoDir.'/'.$username)) {
            $email = $username.'@localhost';
            $repo = new Git\Repo($this->repoDir.'/'.$username.'/'.$repoName.'.git');
            $repo->setUser($username, $email);
            foreach ($table->getHash() as $row) {
                $repo->add($row['filename'], $row['content']);
            }
            $repo->save($commitMessage);
        }
    }

    /**
     * @Given /^I should see a link "([^"]*)" to "([^"]*)"$/
     */
    public function iShouldSeeALinkTo($name, $url)
    {
        return new Given('I should see a link "'.$name.'" to "'.$url.'" in the "body" element');
    }

    /**
     * @Given /^I should see a link "([^"]*)" to "([^"]*)" in the "([^"]*)" element$/
     */
    public function iShouldSeeALinkToInTheElement($name, $url, $element)
    {
        if (!$this->getSession()->getPage()->find('xpath', '//'.$element.'//a[@href=\''.$url.'\' and text()=\''.$name.'\']')) {
            throw new Exception('Could not find a link "'.$url.'" with the link text "'.$name.'"');
        }
    }

    /**
     * @Given /^I should see a link "([^"]*)" to pattern "([^"]*)" in the "([^"]*)" element$/
     */
    public function iShouldSeeALinkToPatternInTheElement($name, $regex, $element)
    {
        $el = $this->getSession()->getPage()->find('xpath', '//'.$element.'//a[text()=\''.$name.'\']');
        if (!$el || !preg_match('|^'.$regex.'$|', $el->getAttribute('href'))) {
            throw new Exception('Could not find matching link with the link text "'.$name.'"');
        }
    }

    /**
     * @Then /^I should see a the basic nav button$/
     */
    public function iShouldSeeATheBasicNavButton()
    {
        return new Then('I should see an "a#nav-button[href=\'/\']" element');
    }

}
