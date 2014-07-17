Feature:
    As a user
    I should be able to see a branches merges

    Scenario: View a list of merges
        Given I send a GET request on "/users/peej/repos/test/branches/master/merges"
        Then response property "_links->self->href" should be "/users/peej/repos/test/branches/master/merges"
        And the header "Content-Type" should be equal to "application/hal+yaml"
        And response property "_links->branch->href" should be "/users/peej/repos/test/branches/master/merges/branch"
