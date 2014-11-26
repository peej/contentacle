Feature:
    As a user
    I should be able to see users

    Scenario: Link to itself
        When I send a GET request to "/users.yaml"
        Then the header "Content-Type" should be equal to "application/hal+yaml"
        And response property "_links->self->href" should be "/users.yaml"

    Scenario: Link to documentation
        When I send a GET request to "/users.yaml"
        Then response property "_links->cont:doc->href" should be "/rels/users"

    Scenario: Have the correct HTTP methods
        Given I send an OPTIONS request to "/users"
        Then the "Allow" response header should be "OPTIONS,GET,POST"
        Given I send an OPTIONS request to "/users/peej"
        Then the "Allow" response header should be "OPTIONS,GET,PATCH,DELETE"

    Scenario: View a list of users
        When I send a GET request to "/users.yaml"
        Then the header "Content-Type" should be equal to "application/hal+yaml"
        And response property "_embedded->cont:user->1->username" should be "peej"
        And response property "_embedded->cont:user->1->name" should be "Paul James"
        And response property "_embedded->cont:user->1->_links->self->href" should be "/users/peej.yaml"

    Scenario: Search for users
        When I send a GET request to "/users.yaml?q=peej"
        Then response property "_embedded->cont:user->0->username" should be "peej"

    Scenario: View a users details
        When I send a GET request to "/users/peej.yaml"
        Then the response status code should be 200
        And the header "Content-Type" should be equal to "application/hal+yaml"
        And response property "username" should be "peej"
        And response property "name" should be "Paul James"
        And response property "email" should be "paul@peej.co.uk"
        And response property "_links->self->href" should be "/users/peej.yaml"
        And response property "_links->cont:doc->href" should be "/rels/user"
        And response property "_embedded->cont:repo->1->name" should be "test"
        And response property "_embedded->cont:repo->1->_links->self->href" should be "/users/peej/repos/test.yaml"

    Scenario: User has a default email address if not created with one
        When I send a GET request to "/users/empty.yaml"
        Then response property "email" should be "empty@localhost"

    Scenario: Receive a 404 for a non-existant user
        When I send a GET request to "/users/missing"
        Then the response status code should be 404
        And the directory "missing" should not exist

    Scenario: Create a user
        Given I add "Content-Type" header equal to "application/json"
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
        Then the response status code should be 200
        And the header "Content-Type" should be equal to "application/hal+yaml"
        And response property "username" should be "test1"
        And response property "password" should be "118b32994e63fd4a3ff1dd091d2e859d9fa66811"
        And response property "email" should be "test1@localhost"

    Scenario: Try to create an invalid user
        Given I add "Content-Type" header equal to "application/json"
        When I send a POST request to "/users" with body:
            """
            {
                "username": "***",
                "password": "test1"
            }
            """
        Then the response status code should be 400
        And the header "Content-Type" should be equal to "application/hal+yaml"
        And response property "_embedded->cont:error->0->logref" should be "username"
        And response property "_embedded->cont:error->1->logref" should be "name"

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
        And the header "Content-Type" should be equal to "application/hal+yaml"
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

    Scenario: Navigate to a user
        Given I am on the homepage
        When I follow the "cont:users" relation
        And I follow the 2nd "cont:user" relation
        Then the response status code should be 200
        And response property "username" should be "peej"
        And response property "name" should be "Paul James"

    Scenario: The cont:users link relation has documentation
        Given I send a GET request to "/users"
        When I uncurie the "cont:users" relation
        Then the response status code should be 200
        And response property "get->description" should exist
        And response property "get->response" should contain "200 OK"
        And response property "get->field->username" should exist
        And response property "get->field->password" should exist
        And response property "get->field->name" should exist
        And response property "get->field->email" should exist
        And response property "get->links->self" should exist
        And response property "get->links->cont:doc" should exist
        And response property "get->embeds->cont:user" should exist
        And response property "get->provides" should contain "application/hal+yaml"
        And response property "get->provides" should contain "application/hal+json"
        And response property "post->description" should exist
        And response property "post->response" should contain "201 Created"
        And response property "post->response" should contain "400 Bad request"
        And response property "post->field->username" should exist
        And response property "post->field->password" should exist
        And response property "post->field->name" should exist
        And response property "post->field->email" should exist
        And response property "post->header->Location" should exist
        And response property "post->embeds->cont:error" should exist
        And response property "post->accepts" should contain "application/yaml"
        And response property "post->accepts" should contain "application/json"
        And response property "post->provides" should contain "application/hal+yaml"
        And response property "post->provides" should contain "application/hal+json"

    Scenario: The cont:user link relation has documentation
        Given I send a GET request to "/users/peej"
        When I uncurie the "cont:user" relation
        Then the response status code should be 200
        And response property "get->description" should exist
        And response property "get->response" should contain "200 OK"
        And response property "get->field->username" should exist
        And response property "get->field->password" should exist
        And response property "get->field->name" should exist
        And response property "get->field->email" should exist
        And response property "get->links->self" should exist
        And response property "get->links->cont:doc" should exist
        And response property "get->links->cont:repos" should exist
        And response property "get->embeds->cont:repo" should exist
        And response property "get->provides" should contain "application/hal+yaml"
        And response property "get->provides" should contain "application/hal+json"
        And response property "patch->description" should exist
        And response property "patch->response" should contain "200 OK"
        And response property "patch->field->username" should exist
        And response property "patch->field->password" should exist
        And response property "patch->field->name" should exist
        And response property "patch->field->email" should exist
        And response property "patch->links->self" should exist
        And response property "patch->links->cont:doc" should exist
        And response property "patch->links->cont:repos" should exist
        And response property "patch->embeds->cont:repo" should exist
        And response property "patch->accepts" should contain "application/json-patch+yaml"
        And response property "patch->accepts" should contain "application/json-patch+json"
        And response property "patch->provides" should contain "application/hal+yaml"
        And response property "patch->provides" should contain "application/hal+json"
        And response property "delete->description" should exist
        And response property "delete->response" should contain "204 No content"