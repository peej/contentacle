Feature:
    As a user
    I should be able to see a repos branches

    Scenario: View a list of branches
        Given I send a GET request on "/users/peej/repos/test/branches"
        Then response property "_links->self->href" should be "/users/peej/repos/test/branches"
        And the header "Content-Type" should be equal to "application/hal+yaml"
        And response property "_links->cont:doc->href" should be "/rels/branches"
        And response property "_embedded->cont:branch->0->_links->self->href" should be "/users/peej/repos/test/branches/branch"
        And response property "_embedded->cont:branch->0->name" should be "branch"
        And response property "_embedded->cont:branch->1->_links->self->href" should be "/users/peej/repos/test/branches/master"
        And response property "_embedded->cont:branch->1->name" should be "master"

    Scenario: View a branches details
        Given I send a GET request on "/users/peej/repos/test/branches/master"
        Then response property "_links->self->href" should be "/users/peej/repos/test/branches/master"
        And the header "Content-Type" should be equal to "application/hal+yaml"
        And response property "name" should be "master"
        And response property "repo" should be "test"
        And response property "username" should be "peej"
        And response property "_links->cont:doc->href" should be "/rels/branch"
        And response property "_links->cont:document->href" should be "/users/peej/repos/test/branches/master/documents"
        And response property "_links->cont:commits->href" should be "/users/peej/repos/test/branches/master/commits"
        And response property "_links->cont:merges->href" should be "/users/peej/repos/test/branches/master/merges"

    Scenario: Have the correct HTTP methods
        Given I send an OPTIONS request to "/users/peej/repos/test/branches"
        Then the "Allow" response header should be "OPTIONS,GET,POST"
        Given I send an OPTIONS request to "/users/peej/repos/test/branches/master"
        Then the "Allow" response header should be "OPTIONS,GET,PATCH,DELETE"

    Scenario: Recieve a 404 for a non-existant branch
        When I send a GET request to "/users/peej/repos/test/branches/missing"
        Then the response status code should be 404

    Scenario: Recieve a 404 when trying to patch a non-existant branch
        Given I add "Content-Type" header equal to "application/json-patch+json"
        And I add "Authorization" header equal to "Basic cGVlajp0ZXN0"
        When I send a PATCH request to "/users/peej/repos/test/branches/missing" with body:
            """
            [{
                "op": "replace",
                "path": "name",
                "value": "renamed-branch"
            }]
            """
        Then the response status code should be 404

    Scenario: Recieve a 404 when trying to delete a non-existant branch
        Given I add "Authorization" header equal to "Basic cGVlajp0ZXN0"
        When I send a DELETE request to "/users/peej/repos/test/branches/missing"
        Then the response status code should be 404

    Scenario: Create a branch
        Given I add "Content-Type" header equal to "application/json"
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
        And the header "Content-Type" should be equal to "application/hal+yaml"
        And response property "name" should be "another"

    Scenario: Try to create an invalid branch
        Given I add "Content-Type" header equal to "application/json"
        And I add "Authorization" header equal to "Basic cGVlajp0ZXN0"
        When I send a POST request to "/users/peej/repos/test/branches" with body:
            """
            {
                "name": "***"
            }
            """
        Then the response status code should be 400
        And the header "Content-Type" should be equal to "application/hal+yaml"
        And response property "_embedded->cont:error->0->logref" should be "name"

    Scenario: Rename a branch
        Given I add "Content-Type" header equal to "application/json-patch+json"
        And I add "Authorization" header equal to "Basic cGVlajp0ZXN0"
        When I send a PATCH request to "/users/peej/repos/test/branches/branch" with body:
            """
            [{
                "op": "replace",
                "path": "name",
                "value": "renamed-branch"
            }]
            """
        Then the response status code should be 200
        And the header "Content-Type" should be equal to "application/hal+yaml"
        And response property "name" should be "renamed-branch"
        When I send a GET request to "/users/peej/repos/test/branches/renamed-branch"
        And the header "Content-Type" should be equal to "application/hal+yaml"
        And response property "name" should be "renamed-branch"

    Scenario: Rename master branch
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
        And the header "Content-Type" should be equal to "application/hal+yaml"
        And response property "name" should be "not-master"
        When I send a GET request to "/users/peej/repos/test/branches/not-master"
        And the header "Content-Type" should be equal to "application/hal+yaml"
        And response property "name" should be "not-master"

    Scenario: Can not rename another branch to same name as another branch
        Given I add "Content-Type" header equal to "application/json-patch+json"
        And I add "Authorization" header equal to "Basic cGVlajp0ZXN0"
        When I send a PATCH request to "/users/peej/repos/test/branches/branch" with body:
            """
            [{
                "op": "replace",
                "path": "name",
                "value": "master"
            }]
            """
        Then the response status code should be 400
        And the header "Content-Type" should be equal to "application/hal+yaml"
        And response property "_embedded->cont:error->0->logref" should be "name"
        And response property "_embedded->cont:error->0->message" should be "A branch named 'master' already exists"
    
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

    Scenario: Navigate to a branch
        Given I am on the homepage
        When I follow the "cont:users" relation
        And I follow the 2nd "cont:user" relation
        And I follow the 2nd "cont:repo" relation
        And I follow the 2nd "cont:branch" relation
        Then the response status code should be 200
        And response property "name" should be "master"

    Scenario: The cont:branches link relation has documentation
        Given I send a GET request to "/users/peej/repos/test/branches"
        When I uncurie the "cont:branches" relation
        Then the response status code should be 200
        And response property "get->description" should exist
        And response property "get->response" should contain "200 OK"
        And response property "get->field->name" should exist
        And response property "get->links->self" should exist
        And response property "get->links->cont:doc" should exist
        And response property "get->embeds->cont:branch" should exist
        And response property "get->provides" should contain "application/hal+yaml"
        And response property "get->provides" should contain "application/hal+json"
        And response property "post->description" should exist
        And response property "post->field->name" should exist
        And response property "post->secure" should exist
        And response property "post->response" should contain "201 Created"
        And response property "post->response" should contain "400 Bad request"
        And response property "post->header->Location" should exist
        And response property "post->embeds->cont:error" should exist
        And response property "post->accepts" should contain "application/yaml"
        And response property "post->accepts" should contain "application/json"
        And response property "post->provides" should contain "application/hal+yaml"
        And response property "post->provides" should contain "application/hal+json"

    Scenario: The cont:branch link relation has documentation
        Given I send a GET request to "/users/peej/repos/test/branches/master"
        When I uncurie the "cont:branch" relation
        Then the response status code should be 200
        And response property "get->description" should exist
        And response property "get->response" should contain "200 OK"
        And response property "get->field->name" should exist
        And response property "get->field->repo" should exist
        And response property "get->field->username" should exist
        And response property "get->links->self" should exist
        And response property "get->links->cont:doc" should exist
        And response property "get->links->cont:commits" should exist
        And response property "get->links->cont:document" should exist
        And response property "get->links->cont:merges" should exist
        And response property "get->provides" should contain "application/hal+yaml"
        And response property "get->provides" should contain "application/hal+json"
        And response property "patch->description" should exist
        And response property "patch->response" should contain "200 OK"
        And response property "patch->response" should contain "400 Bad request"
        And response property "patch->field->name" should exist
        And response property "patch->links->self" should exist
        And response property "patch->links->cont:doc" should exist
        And response property "patch->links->cont:commits" should exist
        And response property "patch->links->cont:document" should exist
        And response property "patch->links->cont:merges" should exist
        And response property "patch->accepts" should contain "application/json-patch+yaml"
        And response property "patch->accepts" should contain "application/json-patch+json"
        And response property "patch->provides" should contain "application/hal+yaml"
        And response property "patch->provides" should contain "application/hal+json"
        And response property "delete->description" should exist
        And response property "delete->response" should contain "204 No content"
        And response property "delete->response" should contain "400 Bad request"