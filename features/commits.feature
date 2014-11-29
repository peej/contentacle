Feature:
    As a user
    I should be able to see a branches commits

    Scenario: View a list of commits
        Given I send a GET request on "/users/peej/repos/test/branches/master/commits"
        Then the response status code should be 200
        And the header "Content-Type" should be equal to "application/hal+yaml"
        And response property "_links->self->href" should be "/users/peej/repos/test/branches/master/commits"
        And response property "_links->cont:doc->href" should be "/rels/commits"
        And response property "_embedded->cont:commit->0->sha" should be sha 4
        And response property "_embedded->cont:commit->1->sha" should be sha 3
        And response property "_embedded->cont:commit->2->sha" should be sha 1

    Scenario: View a commit
        Given I send a GET request on "/users/peej/repos/test/branches/master/commits/{sha}" with sha 1
        Then the response status code should be 200
        And the header "Content-Type" should be equal to "application/hal+yaml"
        And response property "_links->cont:doc->href" should be "/rels/commit"
        And response property "_links->cont:user->href" should be "/users/peej"
        And response property "sha" should be sha 1
        And response property "email" should be "paul@peej.co.uk"
        And response property "username" should be "peej"
        And response property "author" should be "Paul James"
        And response property "files" should contain "contentacle.yaml"
        And response property "_links->cont:document->href" should be "/users/peej/repos/test/branches/master/documents/contentacle.yaml"

    Scenario: Have the correct HTTP methods
        Given I send an OPTIONS request to "/users/peej/repos/test/branches/master/commits"
        Then the "Allow" response header should be "OPTIONS,GET"
        Given I send an OPTIONS request to "/users/peej/repos/test/branches/master/commits/{sha}" with sha 1
        Then the "Allow" response header should be "OPTIONS,GET"

    Scenario: Commit should link to all documents it contains changes for
        Given I send a GET request on "/users/peej/repos/test/branches/master/commits/{sha}" with sha 3
        And response property "_links->cont:document->0->href" should be "/users/peej/repos/test/branches/master/documents/adir/emptyFile.txt"
        And response property "_links->cont:document->1->href" should be "/users/peej/repos/test/branches/master/documents/afile.txt"
        And response property "_links->cont:document->2->href" should be "/users/peej/repos/test/branches/master/documents/anotherFile.txt"

    Scenario: 404 for a non-existant commit
        Given I send a GET request on "/users/peej/repos/test/branches/master/commits/1234567890123546789012345678901234567890"
        Then the response status code should be 404

    Scenario: Revert a single commit
        Given I add "Authorization" header equal to "Basic cGVlajp0ZXN0"
        And I add "Content-Type" header equal to ""
        And I send a POST request on "/users/peej/repos/test/branches/master/commits/{sha}/revert" with sha 3
        Then the response status code should be 201
        And I remember the commit sha from the location header
        Given I send a GET request on "/users/peej/repos/test/branches/master/commits/{sha}" with sha 6
        Then the response status code should be 200
        And the header "Content-Type" should be equal to "application/hal+yaml"
        And response property "message" should be "Undo change {sha}" with sha 3

    Scenario: Revert a single commit with a custom commit message
        Given I add "Content-Type" header equal to "text/plain"
        And I add "Authorization" header equal to "Basic cGVlajp0ZXN0"
        And I send a POST request on "/users/peej/repos/test/branches/master/commits/{sha}/revert" with sha 3 and body:
            """
            Custom commit message
            """
        Then the response status code should be 201
        And I remember the commit sha from the location header
        Given I send a GET request on "/users/peej/repos/test/branches/master/commits/{sha}" with sha 6
        Then the response status code should be 200
        And the header "Content-Type" should be equal to "application/hal+yaml"
        And response property "message" should be "Custom commit message"

    Scenario: Revert a single commit with a custom commit message in JSON
        Given I add "Content-Type" header equal to "application/json"
        And I add "Authorization" header equal to "Basic cGVlajp0ZXN0"
        And I send a POST request on "/users/peej/repos/test/branches/master/commits/{sha}/revert" with sha 3 and body:
            """
            {
                'message': 'Custom commit message'
            }
            """
        Then the response status code should be 201
        And I remember the commit sha from the location header
        Given I send a GET request on "/users/peej/repos/test/branches/master/commits/{sha}" with sha 6
        And the header "Content-Type" should be equal to "application/hal+yaml"
        And response property "message" should be "Custom commit message"

    Scenario: Fail to revert a commit that can't be reverted since it conflicts with a newer commit
        Given I have a commit in "peej/test" with message "Conflict":
            | file               | content         |
            | afile.txt          | Changed content |
        And I add "Authorization" header equal to "Basic cGVlajp0ZXN0"
        And I send a POST request on "/users/peej/repos/test/branches/master/commits/{sha}/revert" with sha 3
        Then the response status code should be 400

    Scenario: Navigate to a commit
        Given I am on the homepage
        When I follow the "cont:users" relation
        And I follow the 2nd "cont:user" relation
        And I follow the 2nd "cont:repo" relation
        And I follow the 2nd "cont:branch" relation
        And I follow the "cont:commits" relation
        And I follow the 1st "cont:commit" relation
        Then the response status code should be 200
        And response property "message" should be "2nd commit"

    Scenario: The cont:commits link relation has documentation
        Given I send a GET request to "/users/peej/repos/test/branches/master/commits"
        When I uncurie the "cont:commits" relation
        Then the response status code should be 200
        And response property "get->description" should exist
        And response property "get->response" should contain "200 OK"
        And response property "get->links->self" should exist
        And response property "get->links->cont:doc" should exist
        And response property "get->embeds->cont:commit" should exist
        And response property "get->provides" should contain "application/hal+yaml"
        And response property "get->provides" should contain "application/hal+json"

    Scenario: The cont:commit link relation has documentation
        Given I send a GET request to "/users/peej/repos/test/branches/master/commits/{sha}" with sha 1
        When I uncurie the "cont:commit" relation
        Then the response status code should be 200
        And response property "get->description" should exist
        And response property "get->response" should contain "200 OK"
        And response property "get->field->sha" should exist
        And response property "get->field->parents" should exist
        And response property "get->field->message" should exist
        And response property "get->field->date" should exist
        And response property "get->field->username" should exist
        And response property "get->field->author" should exist
        And response property "get->field->email" should exist
        And response property "get->field->files" should exist
        And response property "get->field->diff" should exist
        And response property "get->links->self" should exist
        And response property "get->links->cont:doc" should exist
        And response property "get->links->cont:user" should exist
        And response property "get->links->cont:document" should exist
        And response property "get->provides" should contain "application/hal+yaml"
        And response property "get->provides" should contain "application/hal+json"