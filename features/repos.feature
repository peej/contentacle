Feature:
    As a user
    I should be able to see a users repos

    Scenario: View a list of repos
        When I send a GET request on "/users/peej/repos"
        Then response property "_embedded->repos->0->_links->self->href" should be "/users/peej/repos/test"
        And response property "_embedded->repos->0->username" should be "peej"
        And response property "_embedded->repos->0->name" should be "test"
        And response property "_embedded->repos->0->title" should be "Test"
        And response property "_embedded->repos->0->description" should be "No description"

    Scenario: View a repos details
        When I send a GET request on "/users/peej/repos/test"
        Then response property "_links->self->href" should be "/users/peej/repos/test"
        And response property "username" should be "peej"
        And response property "title" should be "Test"
        And response property "description" should be "No description"
        And response property "_embedded->branches->0->name" should be "branch"
        And response property "_embedded->branches->0->_links->self->href" should be "/users/peej/repos/test/branches/branch"
        And response property "_embedded->branches->1->name" should be "master"
        And response property "_embedded->branches->1->_links->self->href" should be "/users/peej/repos/test/branches/master"

    Scenario: Recieve a 404 for a non-existant repo
        When I send a GET request to "/users/peej/repos/missing"
        Then the response status code should be 404

    Scenario: Create a repo
        Given I add "Content-Type" header equal to "contentacle/user+json"
        And I add "Authorization" header equal to "Basic cGVlajp0ZXN0"
        When I send a POST request to "/users/peej/repos" with body:
            """
            {
                "name": "another",
                "title": "Test repo",
                "description": "This is a test repo"
            }
            """
        Then the response status code should be 201
        And the header "Location" should be equal to "/users/peej/repos/another"
        When I send a GET request to "/users/peej/repos/another"
        Then response property "name" should be "another"
        And response property "title" should be "Test repo"
        And response property "description" should be "This is a test repo"

    Scenario: Try to create an invalid repo
        Given I add "Content-Type" header equal to "contentacle/user+json"
        And I add "Authorization" header equal to "Basic cGVlajp0ZXN0"
        When I send a POST request to "/users/peej/repos" with body:
            """
            {
                "name": "***"
            }
            """
        Then the response status code should be 400
        And the header "Content-Type" should be equal to "application/hal+yaml"
        And response property "_embedded->errors->0->logref" should be "name"
        And response property "_embedded->errors->1->logref" should be "title"

    Scenario: Fail to provide correct auth credentials for user when creating a repo
        Given I add "Content-Type" header equal to "contentacle/user+json"
        And I add "Authorization" header equal to "Basic wrong"
        When I send a POST request to "/users/peej/repos" with body:
            """
            {
                "name": "another",
                "title": "Test repo",
                "description": "This is a test repo"
            }
            """
        Then the response status code should be 401

    Scenario: Patch a repo
        Given I add "Content-Type" header equal to "application/json-patch+json"
        And I add "Authorization" header equal to "Basic cGVlajp0ZXN0"
        When I send a PATCH request to "/users/peej/repos/test" with body:
            """
            [{
                "op": "replace",
                "path": "title",
                "value": "Not a test"
            }]
            """
        Then the response status code should be 200
        And response property "username" should be "peej"
        And response property "title" should be "Not a test"
        When I send a GET request to "/users/peej/repos/test"
        And response property "username" should be "peej"
        And response property "name" should be "test"
        And response property "title" should be "Not a test"

    Scenario: Rename/move a repo
        Given I add "Content-Type" header equal to "application/json-patch+json"
        And I add "Authorization" header equal to "Basic cGVlajp0ZXN0"
        When I send a PATCH request to "/users/peej/repos/test" with body:
            """
            [{
                "op": "replace",
                "path": "name",
                "value": "not-a-test"
            }]
            """
        Then the response status code should be 200
        And response property "username" should be "peej"
        And response property "name" should be "not-a-test"
        When I send a GET request to "/users/peej/repos/not-a-test"
        And response property "username" should be "peej"
        And response property "name" should be "not-a-test"

    Scenario: Delete a repo
        Given I add "Authorization" header equal to "Basic cGVlajp0ZXN0"
        When I send a DELETE request to "/users/peej/repos/test"
        Then the response status code should be 204
        When I send a GET request to "/users/peej/repos/test"
        Then the response status code should be 404

    Scenario: Fail to provide correct auth credentials
        Given I add "Authorization" header equal to "Basic wrong"
        When I send a DELETE request to "/users/peej/repos/test"
        Then the response status code should be 401
