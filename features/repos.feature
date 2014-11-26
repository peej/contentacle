Feature:
    As a user
    I should be able to see a users repos

    Scenario: View a list of repos
        When I send a GET request on "/users/peej/repos"
        Then the header "Content-Type" should be equal to "application/hal+yaml"
        And response property "_embedded->cont:repo->1->_links->self->href" should be "/users/peej/repos/test"
        And response property "_embedded->cont:repo->1->username" should be "peej"
        And response property "_embedded->cont:repo->1->name" should be "test"
        And response property "_embedded->cont:repo->1->title" should be "Test"
        And response property "_embedded->cont:repo->1->description" should be "No description"

    Scenario: Have the correct HTTP methods
        Given I send an OPTIONS request to "/users/peej/repos"
        Then the "Allow" response header should be "OPTIONS,GET,POST"
        Given I send an OPTIONS request to "/users/peej/repos/test"
        Then the "Allow" response header should be "OPTIONS,GET,PATCH,PUT,DELETE"

    Scenario: Search for repos
        When I send a GET request to "/users/peej/repos?q=test"
        Then response property "_embedded->cont:repo->0->name" should be "test"

    Scenario: View an empty list of repos
        When I send a GET request on "/users/empty/repos"
        Then response property "_embedded" should not exist
        And response property "_links->self->href" should be "/users/empty/repos"

    Scenario: Link to documentation
        When I send a GET request to "/users/peej/repos"
        Then response property "_links->cont:doc->href" should be "/rels/repos"

    Scenario: View a repos details
        When I send a GET request on "/users/peej/repos/test"
        Then the response status code should be 200
        And the header "Content-Type" should be equal to "application/hal+yaml"
        And response property "_links->self->href" should be "/users/peej/repos/test"
        And response property "_links->cont:doc->href" should be "/rels/repo"
        And response property "username" should be "peej"
        And response property "title" should be "Test"
        And response property "description" should be "No description"
        And response property "_embedded->cont:branch->0->name" should be "branch"
        And response property "_embedded->cont:branch->0->_links->self->href" should be "/users/peej/repos/test/branches/branch"
        And response property "_embedded->cont:branch->1->name" should be "master"
        And response property "_embedded->cont:branch->1->_links->self->href" should be "/users/peej/repos/test/branches/master"
        And the directory "peej/test.git" should exist

    Scenario: Recieve a 404 for a non-existant repo
        When I send a GET request to "/users/peej/repos/missing"
        Then the response status code should be 404
        And the directory "peej/missing.git" should not exist
        Given I add "Content-Type" header equal to "application/json-patch+json"
        And I add "Authorization" header equal to "Basic cGVlajp0ZXN0"
        When I send a PATCH request to "/users/peej/repos/missing" with body:
            """
            [{
                "op": "replace",
                "path": "title",
                "value": "Not a test"
            }]
            """
        Then the response status code should be 404
        And the directory "peej/missing.git" should not exist
        Given I add "Authorization" header equal to "Basic cGVlajp0ZXN0"
        When I send a DELETE request to "/users/peej/repos/missing"
        Then the response status code should be 404
        And the directory "peej/missing.git" should not exist

    Scenario: Create a repo
        Given I add "Content-Type" header equal to "application/json"
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
        Then the response status code should be 200
        And the header "Content-Type" should be equal to "application/hal+yaml"
        And response property "name" should be "another"
        And response property "title" should be "Test repo"
        And response property "description" should be "This is a test repo"

    Scenario: Try to create an invalid repo
        Given I add "Content-Type" header equal to "application/json"
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

    Scenario: Fail to provide correct auth credentials for user when creating a repo
        Given I add "Content-Type" header equal to "application/json"
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
        And the header "Content-Type" should be equal to "application/hal+yaml"
        And response property "username" should be "peej"
        And response property "title" should be "Not a test"
        When I send a GET request to "/users/peej/repos/test"
        Then the response status code should be 200
        And the header "Content-Type" should be equal to "application/hal+yaml"
        And response property "username" should be "peej"
        And response property "name" should be "test"
        And response property "title" should be "Not a test"

    Scenario: Rename a repo
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
        And the header "Content-Type" should be equal to "application/hal+yaml"
        And response property "username" should be "peej"
        And response property "name" should be "not-a-test"
        When I send a GET request to "/users/peej/repos/not-a-test"
        Then the response status code should be 200
        And the header "Content-Type" should be equal to "application/hal+yaml"
        And response property "username" should be "peej"
        And response property "name" should be "not-a-test"

    Scenario: Move a repo to a different user
        Given I add "Content-Type" header equal to "application/json-patch+json"
        And I add "Authorization" header equal to "Basic cGVlajp0ZXN0"
        When I send a PATCH request to "/users/peej/repos/test" with body:
            """
            [{
                "op": "replace",
                "path": "username",
                "value": "empty"
            }]
            """
        Then the response status code should be 200
        And the header "Content-Type" should be equal to "application/hal+yaml"
        And response property "username" should be "empty"
        And response property "name" should be "test"
        When I send a GET request to "/users/peej/repos/test"
        Then the response status code should be 404
        When I send a GET request to "/users/empty/repos/test"
        Then the response status code should be 200
        And the header "Content-Type" should be equal to "application/hal+yaml"
        And response property "username" should be "empty"
        And response property "name" should be "test"

    Scenario: Update a repo
        Given I add "Content-Type" header equal to "application/json"
        And I add "Authorization" header equal to "Basic cGVlajp0ZXN0"
        When I send a PUT request to "/users/peej/repos/test" with body:
            """
            {
                "username": "peej",
                "name": "test",
                "title": "A test repo",
                "description": "This is an updated description"
            }
            """
        Then the response status code should be 200
        And the header "Content-Type" should be equal to "application/hal+yaml"
        And response property "username" should be "peej"
        And response property "name" should be "test"
        And response property "title" should be "A test repo"
        And response property "description" should be "This is an updated description"

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

    Scenario: Navigate to a repo
        Given I am on the homepage
        When I follow the "cont:users" relation
        And I follow the 2nd "cont:user" relation
        And I follow the 2nd "cont:repo" relation
        Then the response status code should be 200
        And response property "username" should be "peej"
        And response property "name" should be "test"
        And response property "title" should be "Test"
        And response property "description" should be "No description"

    Scenario: The cont:repos link relation has documentation
        Given I send a GET request to "/users/peej/repos"
        When I uncurie the "cont:repos" relation
        Then the response status code should be 200
        And response property "get->description" should exist
        And response property "get->response" should contain "200 OK"
        And response property "get->field->username" should exist
        And response property "get->field->name" should exist
        And response property "get->field->title" should exist
        And response property "get->field->description" should exist
        And response property "get->links->self" should exist
        And response property "get->links->cont:doc" should exist
        And response property "get->embeds->cont:repo" should exist
        And response property "get->provides" should contain "application/hal+yaml"
        And response property "get->provides" should contain "application/hal+json"
        And response property "post->description" should exist
        And response property "post->field->name" should exist
        And response property "post->field->title" should exist
        And response property "post->field->description" should exist
        And response property "post->secure" should exist
        And response property "post->response" should contain "201 Created"
        And response property "post->response" should contain "400 Bad request"
        And response property "post->header->Location" should exist
        And response property "post->embeds->cont:error" should exist
        And response property "post->accepts" should contain "application/yaml"
        And response property "post->accepts" should contain "application/json"
        And response property "post->provides" should contain "application/hal+yaml"
        And response property "post->provides" should contain "application/hal+json"

    Scenario: The cont:repo link relation has documentation
        Given I send a GET request to "/users/peej/repos/test"
        When I uncurie the "cont:repo" relation
        Then the response status code should be 200
        And response property "get->description" should exist
        And response property "get->response" should contain "200 OK"
        And response property "get->field->username" should exist
        And response property "get->field->name" should exist
        And response property "get->field->title" should exist
        And response property "get->field->description" should exist
        And response property "get->links->self" should exist
        And response property "get->links->cont:doc" should exist
        And response property "get->links->cont:branches" should exist
        And response property "get->embeds->cont:branch" should exist
        And response property "get->provides" should contain "application/hal+yaml"
        And response property "get->provides" should contain "application/hal+json"
        And response property "patch->description" should exist
        And response property "patch->response" should contain "200 OK"
        And response property "patch->field->username" should exist
        And response property "patch->field->name" should exist
        And response property "patch->field->title" should exist
        And response property "patch->field->description" should exist
        And response property "patch->links->self" should exist
        And response property "patch->links->cont:doc" should exist
        And response property "patch->links->cont:branches" should exist
        And response property "patch->embeds->cont:branch" should exist
        And response property "patch->accepts" should contain "application/json-patch+yaml"
        And response property "patch->accepts" should contain "application/json-patch+json"
        And response property "patch->provides" should contain "application/hal+yaml"
        And response property "patch->provides" should contain "application/hal+json"
        And response property "delete->description" should exist
        And response property "delete->response" should contain "204 No content"