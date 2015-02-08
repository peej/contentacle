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
        Given I add "Content-Type" header equal to "application/hal+yaml"
        And I send a GET request to "/users/peej/repos/test/branches"
        When I uncurie the "cont:branches" relation
        Then the response status code should be 200
        And response property "actions->get->description" should exist
        And response property "actions->get->request->method" should contain "get"
        And response property "actions->get->response->code" should contain "200 OK"
        And response property "actions->get->response->field->name" should exist
        And response property "actions->get->response->links->self" should exist
        And response property "actions->get->response->links->cont:doc" should exist
        And response property "actions->get->response->embeds->cont:branch" should exist
        And response property "actions->get->response->provides" should contain "application/hal+yaml"
        And response property "actions->get->response->provides" should contain "application/hal+json"
        And response property "actions->redirectToMasterBranch->description" should exist
        And response property "actions->redirectToMasterBranch->request->method" should contain "get"
        And response property "actions->redirectToMasterBranch->response->code" should contain "302 Found"
        And response property "actions->createBranch->description" should exist
        And response property "actions->createBranch->request->method" should contain "post"
        And response property "actions->createBranch->request->secure" should exist
        And response property "actions->createBranch->request->field->name" should exist
        And response property "actions->createBranch->request->secure" should exist
        And response property "actions->createBranch->request->accepts" should contain "application/yaml"
        And response property "actions->createBranch->request->accepts" should contain "application/json"
        And response property "actions->createBranch->response->code" should contain "201 Created"
        And response property "actions->createBranch->response->code" should contain "400 Bad request"
        And response property "actions->createBranch->response->header->Location" should exist
        And response property "actions->createBranch->response->embeds->cont:error" should exist
        And response property "actions->createBranch->response->provides" should contain "application/hal+yaml"
        And response property "actions->createBranch->response->provides" should contain "application/hal+json"
    Scenario: The cont:branch link relation has documentation
        Given I add "Content-Type" header equal to "application/hal+yaml"
        And I send a GET request to "/users/peej/repos/test/branches/master"
        When I uncurie the "cont:branch" relation
        Then the response status code should be 200
        And response property "actions->get->description" should exist
        And response property "actions->get->request->method" should contain "get"
        And response property "actions->get->response->code" should contain "200 OK"
        And response property "actions->get->response->field->name" should exist
        And response property "actions->get->response->field->repo" should exist
        And response property "actions->get->response->field->username" should exist
        And response property "actions->get->response->links->self" should exist
        And response property "actions->get->response->links->cont:doc" should exist
        And response property "actions->get->response->links->cont:commits" should exist
        And response property "actions->get->response->links->cont:document" should exist
        And response property "actions->get->response->links->cont:merges" should exist
        And response property "actions->get->response->provides" should contain "application/hal+yaml"
        And response property "actions->get->response->provides" should contain "application/hal+json"
        And response property "actions->renameBranch->description" should exist
        And response property "actions->renameBranch->request->method" should contain "patch"
        And response property "actions->renameBranch->request->secure" should exist
        And response property "actions->renameBranch->request->accepts" should contain "application/json-patch+yaml"
        And response property "actions->renameBranch->request->accepts" should contain "application/json-patch+json"
        And response property "actions->renameBranch->request->field->name" should exist
        And response property "actions->renameBranch->request->secure" should exist
        And response property "actions->renameBranch->response->code" should contain "200 OK"
        And response property "actions->renameBranch->response->code" should contain "400 Bad request"
        And response property "actions->renameBranch->response->provides" should contain "application/hal+yaml"
        And response property "actions->renameBranch->response->provides" should contain "application/hal+json"
        And response property "actions->renameBranch->response->links->self" should exist
        And response property "actions->renameBranch->response->links->cont:doc" should exist
        And response property "actions->renameBranch->response->links->cont:commits" should exist
        And response property "actions->renameBranch->response->links->cont:document" should exist
        And response property "actions->renameBranch->response->links->cont:merges" should exist
        And response property "actions->deleteBranch->description" should exist
        And response property "actions->deleteBranch->request->method" should contain "delete"
        And response property "actions->deleteBranch->response->code" should contain "204 No content"
        And response property "actions->deleteBranch->response->code" should contain "400 Bad request"