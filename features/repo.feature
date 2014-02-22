Feature: Project repos

Background:
    Given a user:
        | username | password | name      |
        | testuser | test     | Test User |
    And a repo "testrepo" for user "testuser" with commit "Test commit":
        | filename      | content        |
        | testfile.txt  | this is a test |
        | dir/indir.txt | in dir file    |

Scenario: View a repo
    Given I am on "/testuser/testrepo"
    Then I should see "testrepo"
    And I should see "Test Repo"
    And I should see "This is a test repo created by the test suite."
    And I should see a link "master" to "/testuser/testrepo/tree/master"
    And I should see a link "testfile.txt" to "/testuser/testrepo/blob/master/testfile.txt"
    And I should see a link "dir" to "/testuser/testrepo/tree/master/dir"
    And I should see a link "Test User" to "/testuser"
    And I should see "Test commit"

Scenario: View a directory
    Given I am on "/testuser/testrepo"
    And I follow "dir"
    Then I should see "testrepo"
    And I should see a link "master" to "/testuser/testrepo/tree/master"
    And I should see a link "dir" to "/testuser/testrepo/tree/master/dir"
    And I should see a link "indir.txt" to "/testuser/testrepo/blob/master/dir/indir.txt"
    And I should see a link "Test User" to "/testuser"
    And I should see a link "Test commit" to pattern "/testuser/testrepo/history/[0-9a-f]{40}" in the "div[@class='commit-metadata']" element
    And I should see a link "Test commit" to pattern "/testuser/testrepo/history/[0-9a-f]{40}" in the "table" element
    And I should see "Test commit"

Scenario: A directory that doesn't exist
    Given I am on "/testuser/testrepo/nothing"
    Then the response status code should be 404
    And I should see "404"

Scenario: View a file
    Given I am on "/testuser/testrepo"
    And I follow "testfile.txt"
    Then I should see "testrepo"
    And I should see "this is a test"
    And I should see a link "Test User" to "/testuser"

Scenario: View commit history
    Given I am on "/testuser/testrepo"
    And I follow "Test commit"
    Then I should see "dir/indir.txt"
    And I should see "in dir file"
    And I should see "testfile.txt"
    And I should see "this is a test"

Scenario: View commit history
    Given a repo "testrepo" for user "testuser" with commit "Another commit":
        | filename      | content        |
        | testfile.txt  | this is a test\nline 2\nline 3\nline 4\nline 5\nline 6\nline 7\nline 8\nline 9\nline 10\nline 11 |
    And I am on "/testuser/testrepo"
    And I follow "Another commit"
    Then I should see "testfile.txt"
    And I should see "this is a test"
    And I should see "line 2"
    And I should see "Commit contains 1 changed file with 10 additions and 0 deletions"