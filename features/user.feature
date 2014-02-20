Feature: User profiles

Background:
    Given a user:
        | username | password | name      | repo |
        | testuser | test     | Test User | test |

Scenario: 
    Given I am on "/testuser"
    Then I should see "testuser"
    And I should see "Test User"
    And I should see a link "test" to "/testuser/test"