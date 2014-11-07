Feature:
    As a user
    I should be able to see users

    Scenario: Link to itself
        When I send a GET request to "/users.yaml"
        Then the header "Content-Type" should be equal to "application/hal+yaml"
        And response property "_links->self->href" should be "/users.yaml"

    Scenario: Provide an add user form
        When I send a GET request to "/users.yaml"
        Then response property "_links->cont:add-user->method" should be "post"
        And response property "_links->cont:add-user->content-type" should contain "contentacle/user+yaml"
        And response property "_links->cont:add-user->content-type" should contain "contentacle/user+json"

    Scenario: View a list of users
        When I send a GET request to "/users.yaml"
        Then the header "Content-Type" should be equal to "application/hal+yaml"
        And response property "_embedded->users->1->username" should be "peej"
        And response property "_embedded->users->1->name" should be "Paul James"
        And response property "_embedded->users->1->_links->self->href" should be "/users/peej.yaml"

    Scenario: Search for users
        When I send a GET request to "/users.yaml?q=peej"
        Then response property "_embedded->users->0->username" should be "peej"

    Scenario: View a users details
        When I send a GET request to "/users/peej.yaml"
        Then the response status code should be 200
        And the header "Content-Type" should be equal to "contentacle/user+yaml"
        And response property "username" should be "peej"
        And response property "name" should be "Paul James"
        And response property "email" should be "paul@peej.co.uk"
        And response property "_links->self->href" should be "/users/peej.yaml"
        And response property "_embedded->repos->1->name" should be "test"
        And response property "_embedded->repos->1->_links->self->href" should be "/users/peej/repos/test.yaml"

    Scenario: Provide an edit user form
        When I send a GET request to "/users/peej"
        Then response property "_links->cont:edit-user->method" should be "patch"
        And response property "_links->cont:edit-user->content-type" should contain "application/json-patch+json"
        And response property "_links->cont:edit-user->content-type" should contain "application/json-patch+yaml"

    Scenario: User has a default email address if not created with one
        When I send a GET request to "/users/empty.yaml"
        Then response property "email" should be "empty@localhost"

    Scenario: Receive a 404 for a non-existant user
        When I send a GET request to "/users/missing"
        Then the response status code should be 404
        And the directory "missing" should not exist

    Scenario: Create a user
        Given I add "Content-Type" header equal to "contentacle/user+json"
        When I send a POST request to "/users" with body:
            """
            {
                "username": "test1",
                "name": "Behat Tester",
                "password": "test1"
            }
            """
        Then the response status code should be 201
        And the header "Location" should be equal to "/users/test1"
        When I send a GET request to "/users/test1"
        Then the header "Content-Type" should be equal to "contentacle/user+yaml"
        And response property "username" should be "test1"
        And response property "password" should be "118b32994e63fd4a3ff1dd091d2e859d9fa66811"
        And response property "email" should be "test1@localhost"

    Scenario: Try to create an invalid user
        Given I add "Content-Type" header equal to "contentacle/user+json"
        When I send a POST request to "/users" with body:
            """
            {
                "username": "***",
                "password": "test1"
            }
            """
        Then the response status code should be 400
        And the header "Content-Type" should be equal to "application/hal+yaml"
        And response property "_embedded->errors->0->logref" should be "username"
        And response property "_embedded->errors->1->logref" should be "name"

    Scenario: Patch a user
        Given I add "Content-Type" header equal to "application/json-patch+json"
        And I add "Authorization" header equal to "Basic cGVlajp0ZXN0"
        When I send a PATCH request to "/users/peej" with body:
            """
            [{
                "op": "replace",
                "path": "name",
                "value": "PJ"
            }]
            """
        Then the response status code should be 200
        And the header "Content-Type" should be equal to "contentacle/user+yaml"
        And response property "username" should be "peej"
        And response property "name" should be "PJ"
        When I send a GET request to "/users/peej"
        And response property "username" should be "peej"
        And response property "name" should be "PJ"

    Scenario: Delete a user
        Given I add "Authorization" header equal to "Basic cGVlajp0ZXN0"
        When I send a DELETE request to "/users/peej"
        Then the response status code should be 204
        When I send a GET request to "/users/peej"
        Then the response status code should be 404

    Scenario: Fail to provide correct auth credentials
        Given I add "Authorization" header equal to "Basic wrong"
        When I send a DELETE request to "/users/peej"
        Then the response status code should be 401
