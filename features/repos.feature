Feature:
    As a user
    I should be able to see a users repos

    Background:
        Given I set the "accept" header to "*/*"

    Scenario: View a list of repos
        Given I send a GET request on "/users/peej/repos"
        Then response property "_embedded->repos->0->_links->self->href" should be "/users/peej/repos/test"
        And response property "_embedded->repos->0->username" should be "peej"
        And response property "_embedded->repos->0->name" should be "test"
        And response property "_embedded->repos->0->title" should be "Test"
        And response property "_embedded->repos->0->description" should be "No description"

    Scenario: View a repos details
        Given I send a GET request on "/users/peej/repos/test"
        Then response property "_links->self->href" should be "/users/peej/repos/test"
        And response property "username" should be "peej"
        And response property "title" should be "Test"
        And response property "description" should be "No description"
        And response property "_embedded->branches->0->name" should be "branch"
        And response property "_embedded->branches->0->_links->self->href" should be "/users/peej/repos/test/branches/branch"
        And response property "_embedded->branches->1->name" should be "master"
        And response property "_embedded->branches->1->_links->self->href" should be "/users/peej/repos/test/branches/master"
