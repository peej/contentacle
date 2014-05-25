Feature:
    As a user
    I should be able to see a repos branches

    Scenario: View a list of branches
        Given I send a GET request on "/users/peej/repos/test/branches"
        Then response property "master->url" should be "/users/peej/repos/test/branches/master"
        And response property "master->name" should be "master"

    Scenario: View a branches details
        Given I send a GET request on "/users/peej/repos/test/branches/master"
        Then response property "branch" should be "master"
        And response property "documents" should be "/users/peej/repos/test/branches/master/documents"
        And response property "commits" should be "/users/peej/repos/test/branches/master/commits"
        And response property "repo" should be "test"
