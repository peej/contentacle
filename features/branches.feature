Feature:
    As a user
    I should be able to see a repos branches

    Scenario: View a list of branches
        Given I send a GET request on "/users/peej/repos/test/branches"
        Then response property "_links->self->href" should be "/users/peej/repos/test/branches"
        And the header "Content-Type" should be equal to "application/hal+yaml"
        And response property "_embedded->branches->0->_links->self->href" should be "/users/peej/repos/test/branches/branch"
        And response property "_embedded->branches->0->name" should be "branch"
        And response property "_embedded->branches->1->_links->self->href" should be "/users/peej/repos/test/branches/master"
        And response property "_embedded->branches->1->name" should be "master"
        
    Scenario: View a branches details
        Given I send a GET request on "/users/peej/repos/test/branches/master"
        Then response property "_links->self->href" should be "/users/peej/repos/test/branches/master"
        And the header "Content-Type" should be equal to "contentacle/branch+yaml"
        And response property "name" should be "master"
        And response property "repo" should be "test"
        And response property "username" should be "peej"
        And response property "_links->cont:documents->href" should be "/users/peej/repos/test/branches/master/documents"
        And response property "_links->cont:commits->href" should be "/users/peej/repos/test/branches/master/commits"
