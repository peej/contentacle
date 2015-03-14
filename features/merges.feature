Feature:
    As a user
    I should be able to see a branches merges

    Scenario: View a list of merges
        Given I send a GET request on "/users/peej/repos/test/branches/master/merges"
        Then response property "_links->self->href" should be "/users/peej/repos/test/branches/master/merges"
        And the content-type response header should be "application/hal+yaml"
        And response property "_links->cont:merge->0->href" should be "/users/peej/repos/test/branches/master/merges/branch"

    Scenario: Have the correct HTTP methods
        Given I send an OPTIONS request to "/users/peej/repos/test/branches/master/merges"
        Then the "Allow" response header should be "OPTIONS,GET"
        Given I send an OPTIONS request to "/users/peej/repos/test/branches/master/merges/branch"
        Then the "Allow" response header should be "OPTIONS,GET,POST"

    Scenario: View a merge that can be merged
        Given I send a GET request on "/users/peej/repos/test/branches/branch/merges/master"
        Then response property "_links->self->href" should be "/users/peej/repos/test/branches/branch/merges/master"
        And the content-type response header should be "application/hal+yaml"
        And response property "canMerge" should be "true"
        And response property "conflicts" should not exist

    Scenario: View a merge that has nothing to merge
        Given I send a GET request on "/users/peej/repos/test/branches/master/merges/branch"
        Then response property "_links->self->href" should be "/users/peej/repos/test/branches/master/merges/branch"
        And the content-type response header should be "application/hal+yaml"
        And response property "canMerge" should be "false"
        And response property "conflicts" should not exist

    Scenario: View a merge that conflicts
        Given I send a GET request on "/users/peej/repos/test/branches/master/merges/unmergable"
        Then response property "_links->self->href" should be "/users/peej/repos/test/branches/master/merges/unmergable"
        And the content-type response header should be "application/hal+yaml"
        And response property "canMerge" should be "false"
        And response property "conflicts->clash.txt->0" should be "1-Clash all over the place"
        And response property "conflicts->clash.txt->1" should be "1+This will clash"

    Scenario: Branch can not be merged with itself
        Given I send a GET request on "/users/peej/repos/test/branches/master/merges/master"
        Then the response status code should be 404

    Scenario: Branch can not be merged with non-existant branch
        Given I send a GET request on "/users/peej/repos/test/branches/master/merges/doesntexist"
        Then the response status code should be 404

    Scenario: Merge a branch
        When I send a POST request on "/users/peej/repos/test/branches/branch/merges/master"
        Then the response status code should be 204
        When I send a GET request on "/users/peej/repos/test/branches/branch/commits"
        Then response property "_embedded->cont:commit->0->message" should be "Merge master into branch"

    Scenario: Fail to merge a merge that conflicts
        When I send a POST request on "/users/peej/repos/test/branches/master/merges/unmergable"
        Then the response status code should be 400

    Scenario: Navigate to a merge
        Given I am on the homepage
        When I follow the "cont:users" relation
        And I follow the 2nd "cont:user" relation
        And I follow the 2nd "cont:repo" relation
        And I follow the 2nd "cont:branch" relation
        And I follow the "cont:merges" relation
        And I follow the 1st "cont:merge" relation
        Then the response status code should be 200
        And response property "canMerge" should be "false"

    Scenario: The cont:merges link relation has documentation
        Given I send a GET request to "/users/peej/repos/test/branches/master"
        When I uncurie the "cont:merges" relation
        Then the response status code should be 200
        And response property "actions->get->description" should exist
        And response property "actions->get->request->method" should contain "get"
        And response property "actions->get->response->code" should contain "200 OK"
        And response property "actions->get->response->links->self" should exist
        And response property "actions->get->response->links->cont:doc" should exist
        And response property "actions->get->response->links->cont:merge" should exist
        And response property "actions->get->response->provides" should contain "application/hal+yaml"
        And response property "actions->get->response->provides" should contain "application/hal+json"

    Scenario: The cont:merge link relation has documentation
        Given I send a GET request to "/users/peej/repos/test/branches/master/merges"
        When I uncurie the "cont:merge" relation
        Then the response status code should be 200
        And response property "actions->get->description" should exist
        And response property "actions->get->request->method" should contain "get"
        And response property "actions->get->response->code" should contain "200 OK"
        And response property "actions->get->response->field->canMerge" should exist
        And response property "actions->get->response->field->conflicts" should exist
        And response property "actions->get->response->links->self" should exist
        And response property "actions->get->response->links->cont:doc" should exist
        And response property "actions->get->response->provides" should contain "application/hal+yaml"
        And response property "actions->get->response->provides" should contain "application/hal+json"
        And response property "actions->post->description" should exist
        And response property "actions->post->request->method" should contain "post"
        And response property "actions->post->response->code" should contain "204 No content"
        And response property "actions->post->response->code" should contain "400 Bad request"
        And response property "actions->post->response->code" should contain "404 Not found"