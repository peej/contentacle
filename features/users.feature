Feature:
    As a user
    I should be able to see users

    Scenario: Link to itself
        When I send a GET request to "/users.yaml"
        Then response property "_links->self->href" should be "/users.yaml"

    Scenario: Provide an add user form
        When I send a GET request to "/users.yaml"
        Then response property "_links->cont:add-user->method" should be "post"
        And response property "_links->cont:add-user->content-type" should contain "contentacle/user+yaml"
        And response property "_links->cont:add-user->content-type" should contain "contentacle/user+json"

    Scenario: View a list of users
        When I send a GET request to "/users.yaml"
        Then response property "_embedded->users->0->username" should be "peej"
        And response property "_embedded->users->0->name" should be "Paul James"
        And response property "_embedded->users->0->_links->self->href" should be "/users/peej.yaml"

    Scenario: View a users details
        When I send a GET request to "/users/peej.yaml"
        Then response property "username" should be "peej"
        And response property "name" should be "Paul James"
        And response property "email" should be "peej@localhost"
        And response property "_links->self->href" should be "/users/peej.yaml"
        And response property "_embedded->repos->0->name" should be "test"
        And response property "_embedded->repos->0->_links->self->href" should be "/users/peej/repos/test.yaml"

    Scenario: Create a user
        Given I add "Content-Type" header equal to "contentacle/user+json"
        When I send a POST request to "/users" with body:
            """
            {
                "username": "test1",
                "name": "Behat Tester",
                "email": "tester@localhost",
                "password": "test1"
            }
            """
        Then the response status code should be 201
        And the header "Location" should be equal to "/users/test1"
        When I send a GET request to "/users/test1"
        Then response property "username" should be "test1"

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
