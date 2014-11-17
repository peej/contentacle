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

    Scenario: Revert a single commit with a custom commit message
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
