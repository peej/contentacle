Feature: Navigation menu

Background:
    Given a user:
        | username | password | name      |
        | testuser | test     | Test User |
    And a repo "testrepo" for user "testuser" with commit "Test commit":
        | filename     | content        |
        | testfile.txt | this is a test |

Scenario: 
    Given I am on the homepage
    Then I should see a the basic nav button

Scenario: 
    Given I am on "/testuser"
    Then I should see "Author" in the "nav" element
    And I should see a link "Profile" to "/testuser" in the "nav" element
    And I should see a link "Settings" to "/settings" in the "nav" element
    And I should see a link "Sign out" to "/signout" in the "nav" element

Scenario: 
    Given I am on "/testuser/testrepo"
    Then I should see "testrepo" in the "nav" element
    And I should see a link "Pages" to "/testuser/testrepo" in the "nav" element
    And I should see a link "Revisions" to "/testuser/testrepo/history" in the "nav" element
    And I should see a link "Stage" to "/testuser/testrepo/stage" in the "nav" element
    And I should see "Author" in the "nav" element
    And I should see a link "Profile" to "/testuser" in the "nav" element
    And I should see a link "Settings" to "/settings" in the "nav" element
    And I should see a link "Sign out" to "/signout" in the "nav" element

Scenario:
    Given I am on "/testuser/testrepo/blob/master/testfile.txt"
    Then I should see "testrepo" in the "nav" element
    And I should see a link "Pages" to "/testuser/testrepo" in the "nav" element
    And I should see a link "Revisions" to "/testuser/testrepo/history" in the "nav" element
    And I should see a link "Stage" to "/testuser/testrepo/stage" in the "nav" element
    And I should see "testfile.txt" in the "nav" element
    And I should see a link "Edit" to "/testuser/testrepo/edit/master/testfile.txt" in the "nav" element
    And I should see a link "Properties" to "/testuser/testrepo/prop/master/testfile.txt" in the "nav" element
    And I should see a link "History" to "/testuser/testrepo/history/master/testfile.txt" in the "nav" element
    And I should see "Author" in the "nav" element
    And I should see a link "Profile" to "/testuser" in the "nav" element
    And I should see a link "Settings" to "/settings" in the "nav" element
    And I should see a link "Sign out" to "/signout" in the "nav" element