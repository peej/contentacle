Feature:
    As a user
    I should be able to see a users repos

    Scenario: View a list of repos
        Given I send a GET request on "/users/peej/repos"
        Then response property "test->url" should be "/users/peej/repos/test"
        And response property "test->username" should be "peej"
        And response property "test->title" should be "Test"
        And response property "test->description" should be "No description"

    Scenario: View a repos details
        Given I send a GET request on "/users/peej/repos/test"
        Then response property "url" should be "/users/peej/repos/test"
        And response property "username" should be "peej"
        And response property "title" should be "Test"
        And response property "description" should be "No description"
        And response property "branches->branch->name" should be "branch"
        And response property "branches->branch->url" should be "/users/peej/repos/test/branches/branch"
        And response property "branches->master->name" should be "master"
        And response property "branches->master->url" should be "/users/peej/repos/test/branches/master"
