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

    Scenario: Recieve a 404 for a non-existant branch
        When I send a GET request to "/users/peej/repos/test/branches/missing"
        Then the response status code should be 404

    Scenario: Create a branch
        Given I add "Content-Type" header equal to "contentacle/branch+json"
        And I add "Authorization" header equal to "Basic cGVlajp0ZXN0"
        When I send a POST request to "/users/peej/repos/test/branches" with body:
            """
            {
                "name": "another"
            }
            """
        Then the response status code should be 201
        And the header "Location" should be equal to "/users/peej/repos/test/branches/another"
        When I send a GET request to "/users/peej/repos/test/branches/another"
        Then the response status code should be 200
        And response property "name" should be "another"

    Scenario: Try to create an invalid branch
        Given I add "Content-Type" header equal to "contentacle/branch+json"
        And I add "Authorization" header equal to "Basic cGVlajp0ZXN0"
        When I send a POST request to "/users/peej/repos/test/branches" with body:
            """
            {
                "name": "***"
            }
            """
        Then the response status code should be 400
        And the header "Content-Type" should be equal to "application/hal+yaml"
        And response property "_embedded->errors->0->logref" should be "name"

    @wip
    Scenario: Rename a branch
        Given I add "Content-Type" header equal to "application/json-patch+json"
        And I add "Authorization" header equal to "Basic cGVlajp0ZXN0"
        When I send a PATCH request to "/users/peej/repos/test/branches/master" with body:
            """
            [{
                "op": "replace",
                "path": "name",
                "value": "not-master"
            }]
            """
        Then the response status code should be 200
        And response property "name" should be "not-master"
        When I send a GET request to "/users/peej/repos/not-master"
        And response property "name" should be "not-master"

    Scenario: Delete a branch
        Given I add "Authorization" header equal to "Basic cGVlajp0ZXN0"
        When I send a DELETE request to "/users/peej/repos/test/branches/branch"
        Then the response status code should be 204
        When I send a GET request to "/users/peej/repos/test/branches/branch"
        Then the response status code should be 404

    Scenario: Fail to delete master branch
        Given I add "Authorization" header equal to "Basic cGVlajp0ZXN0"
        When I send a DELETE request to "/users/peej/repos/test/branches/master"
        Then the response status code should be 400

    Scenario: Fail to delete missing branch
        Given I add "Authorization" header equal to "Basic cGVlajp0ZXN0"
        When I send a DELETE request to "/users/peej/repos/test/branches/missing"
        Then the response status code should be 404