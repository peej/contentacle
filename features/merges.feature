Feature:
    As a user
    I should be able to see a branches merges

    Scenario: View a list of merges
        Given I send a GET request on "/users/peej/repos/test/branches/master/merges"
        Then response property "_links->self->href" should be "/users/peej/repos/test/branches/master/merges"
        And the header "Content-Type" should be equal to "application/hal+yaml"
        And response property "_links->branch->href" should be "/users/peej/repos/test/branches/master/merges/branch"

    Scenario: View a merge that can be merged
        Given I send a GET request on "/users/peej/repos/test/branches/master/merges/branch"
        Then response property "_links->self->href" should be "/users/peej/repos/test/branches/master/merges/branch"
        And the header "Content-Type" should be equal to "contentacle/merge+yaml"
        And response property "canMerge" should be "true"

    Scenario: View a merge that can NOT be merged
        Given I send a GET request on "/users/peej/repos/test/branches/master/merges/unmergable"
        Then response property "_links->self->href" should be "/users/peej/repos/test/branches/master/merges/unmergable"
        And the header "Content-Type" should be equal to "contentacle/merge+yaml"
        And response property "canMerge" should be "false"
        And response property "conflicts->clash.txt->0" should be "1-Clash all over the place"
        And response property "conflicts->clash.txt->1" should be "1+This will clash"

    Scenario: Branch can not be merged with itself
        Given I send a GET request on "/users/peej/repos/test/branches/master/merges/master"
        Then the response status code should be 404

    Scenario: Branch can not be merged with non-existant branch
        Given I send a GET request on "/users/peej/repos/test/branches/master/merges/doesntexist"
        Then the response status code should be 404
