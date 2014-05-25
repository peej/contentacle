Feature:
    As a user
    I should be able to see users

    Scenario: View a list of users
        Given I send a GET request on "/users.yml"
        Then response property "peej->url" should be "/users/peej"
        And response property "peej->username" should be "peej"
        And response property "peej->name" should be "Paul James"

    Scenario: View a users details
        Given I send a GET request on "/users/peej.json"
        Then response property "url" should be "/users/peej"
        And response property "username" should be "peej"
        And response property "name" should be "Paul James"
        And response property "email" should be "peej@localhost"
        And response property "repos->test->name" should be "test"
        And response property "repos->test->url" should be "/users/peej/repos/test"
