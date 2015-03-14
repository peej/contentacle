Feature:
    As a user
    I should be able to see a users repos

    Scenario: View a list of repos
        When I send a GET request on "/users/peej/repos"
        Then the content-type response header should be "application/hal+yaml"
        And response property "_embedded->cont:repo->1->_links->self->href" should be "/users/peej/repos/test"
        And response property "_embedded->cont:repo->1->username" should be "peej"
        And response property "_embedded->cont:repo->1->name" should be "test"
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
        And the content-type response header should be "application/hal+yaml"
        And response property "_links->self->href" should be "/users/peej/repos/test"
        And response property "_links->cont:doc->href" should be "/rels/repo"
        And response property "username" should be "peej"
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
                "path": "description",
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
                "description": "This is a test repo"
            }
            """
        Then the response status code should be 201
        And the header "Location" should be equal to "/users/peej/repos/another"
        When I send a GET request to "/users/peej/repos/another"
        Then the response status code should be 200
        And the content-type response header should be "application/hal+yaml"
        And response property "name" should be "another"
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
        And the content-type response header should be "application/hal+yaml"
        And response property "_embedded->errors->0->logref" should be "name"

    Scenario: Fail to provide correct auth credentials for user when creating a repo
        Given I add "Content-Type" header equal to "application/json"
        And I add "Authorization" header equal to "Basic wrong"
        When I send a POST request to "/users/peej/repos" with body:
            """
            {
                "name": "another",
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
                "path": "description",
                "value": "Not a test"
            }]
            """
        Then the response status code should be 200
        And the content-type response header should be "application/hal+yaml"
        And response property "username" should be "peej"
        And response property "description" should be "Not a test"
        When I send a GET request to "/users/peej/repos/test"
        Then the response status code should be 200
        And the content-type response header should be "application/hal+yaml"
        And response property "username" should be "peej"
        And response property "name" should be "test"
        And response property "description" should be "Not a test"

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
        And the content-type response header should be "application/hal+yaml"
        And response property "username" should be "peej"
        And response property "name" should be "not-a-test"
        When I send a GET request to "/users/peej/repos/not-a-test"
        Then the response status code should be 200
        And the content-type response header should be "application/hal+yaml"
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
        And the content-type response header should be "application/hal+yaml"
        And response property "username" should be "empty"
        And response property "name" should be "test"
        When I send a GET request to "/users/peej/repos/test"
        Then the response status code should be 404
        When I send a GET request to "/users/empty/repos/test"
        Then the response status code should be 200
        And the content-type response header should be "application/hal+yaml"
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
                "description": "This is an updated description"
            }
            """
        Then the response status code should be 200
        And the content-type response header should be "application/hal+yaml"
        And response property "username" should be "peej"
        And response property "name" should be "test"
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
        And response property "description" should be "No description"

    Scenario: The cont:repos link relation has documentation
        Given I send a GET request to "/users/peej/repos"
        When I uncurie the "cont:repos" relation
        Then the response status code should be 200
        And response property "actions->get->description" should exist
        And response property "actions->get->request->method" should contain "get"
        And response property "actions->get->response->code" should contain "200 OK"
        And response property "actions->get->response->field->username" should exist
        And response property "actions->get->response->field->name" should exist
        And response property "actions->get->response->field->description" should exist
        And response property "actions->get->response->links->self" should exist
        And response property "actions->get->response->links->cont:doc" should exist
        And response property "actions->get->response->embeds->cont:repo" should exist
        And response property "actions->get->response->provides" should contain "application/hal+yaml"
        And response property "actions->get->response->provides" should contain "application/hal+json"
        And response property "actions->createRepo->description" should exist
        And response property "actions->createRepo->request->method" should contain "post"
        And response property "actions->createRepo->request->secure" should exist
        And response property "actions->createRepo->request->accepts" should contain "application/yaml"
        And response property "actions->createRepo->request->accepts" should contain "application/json"
        And response property "actions->createRepo->request->field->name" should exist
        And response property "actions->createRepo->request->field->description" should exist
        And response property "actions->createRepo->request->secure" should exist
        And response property "actions->createRepo->response->code" should contain "201 Created"
        And response property "actions->createRepo->response->code" should contain "400 Bad request"
        And response property "actions->createRepo->response->header->Location" should exist
        And response property "actions->createRepo->response->embeds->cont:error" should exist
        And response property "actions->createRepo->response->provides" should contain "application/hal+yaml"
        And response property "actions->createRepo->response->provides" should contain "application/hal+json"

    Scenario: The cont:repo link relation has documentation
        Given I send a GET request to "/users/peej/repos/test"
        When I uncurie the "cont:repo" relation
        Then the response status code should be 200
        And response property "actions->get->description" should exist
        And response property "actions->get->request->method" should contain "get"
        And response property "actions->get->response->code" should contain "200 OK"
        And response property "actions->get->response->field->username" should exist
        And response property "actions->get->response->field->name" should exist
        And response property "actions->get->response->field->description" should exist
        And response property "actions->get->response->links->self" should exist
        And response property "actions->get->response->links->cont:doc" should exist
        And response property "actions->get->response->links->cont:branches" should exist
        And response property "actions->get->response->embeds->cont:branch" should exist
        And response property "actions->get->response->provides" should contain "application/hal+yaml"
        And response property "actions->get->response->provides" should contain "application/hal+json"
        And response property "actions->redirectToMasterBranch->description" should exist
        And response property "actions->redirectToMasterBranch->request->method" should contain "get"
        And response property "actions->redirectToMasterBranch->response->code" should contain "302 Found"
        And response property "actions->patchRepo->description" should exist
        And response property "actions->patchRepo->request->method" should contain "patch"
        And response property "actions->patchRepo->request->secure" should exist
        And response property "actions->patchRepo->request->field->username" should exist
        And response property "actions->patchRepo->request->field->name" should exist
        And response property "actions->patchRepo->request->field->description" should exist
        And response property "actions->patchRepo->request->accepts" should contain "application/json-patch+yaml"
        And response property "actions->patchRepo->request->accepts" should contain "application/json-patch+json"
        And response property "actions->patchRepo->response->code" should contain "200 OK"
        And response property "actions->patchRepo->response->links->self" should exist
        And response property "actions->patchRepo->response->links->cont:doc" should exist
        And response property "actions->patchRepo->response->links->cont:branches" should exist
        And response property "actions->patchRepo->response->embeds->cont:branch" should exist
        And response property "actions->patchRepo->response->provides" should contain "application/hal+yaml"
        And response property "actions->patchRepo->response->provides" should contain "application/hal+json"
        And response property "actions->updateRepo->description" should exist
        And response property "actions->updateRepo->request->method" should contain "put"
        And response property "actions->updateRepo->request->secure" should exist
        And response property "actions->updateRepo->request->field->username" should exist
        And response property "actions->updateRepo->request->field->name" should exist
        And response property "actions->updateRepo->request->field->description" should exist
        And response property "actions->updateRepo->request->accepts" should contain "application/hal+yaml"
        And response property "actions->updateRepo->request->accepts" should contain "application/hal+json"
        And response property "actions->updateRepo->response->code" should contain "200 OK"
        And response property "actions->updateRepo->response->links->self" should exist
        And response property "actions->updateRepo->response->links->cont:doc" should exist
        And response property "actions->updateRepo->response->links->cont:branches" should exist
        And response property "actions->updateRepo->response->embeds->cont:branch" should exist
        And response property "actions->updateRepo->response->provides" should contain "application/hal+yaml"
        And response property "actions->updateRepo->response->provides" should contain "application/hal+json"
        And response property "actions->deleteRepo->description" should exist
        And response property "actions->deleteRepo->request->method" should contain "delete"
        And response property "actions->deleteRepo->request->secure" should exist
        And response property "actions->deleteRepo->response->code" should contain "204 No content"