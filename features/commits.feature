Feature:
    As a user
    I should be able to see a branches commits

    Scenario: View a list of commits
        Given I send a GET request on "/users/peej/repos/test/branches/master/commits"
        Then response property "_links->self->href" should be "/users/peej/repos/test/branches/master/commits"
        And response property "_links->cont:commit->method" should be "post"
        And response property "_links->cont:commit->content-type" should contain "contentacle/commit+yaml"
        And response property "_embedded->commits->0->sha" should be sha 4
        And response property "_embedded->commits->1->sha" should be sha 3
        And response property "_embedded->commits->2->sha" should be sha 1

    Scenario: View a commit
        Given I send a GET request on "/users/peej/repos/test/branches/master/commits/{sha}" with sha 1
        Then response property "sha" should be sha 1
        And response property "email" should be "paul@peej.co.uk"
        And response property "username" should be "peej"
        And response property "author" should be "Paul James"
        And response property "files" should contain "contentacle.yaml"

    Scenario: 404 for a non-existant commit
        Given I send a GET request on "/users/peej/repos/test/branches/master/commits/1234567890123546789012345678901234567890"
        Then the response status code should be 404

    Scenario: Revert a single commit
        Given I add "Content-Type" header equal to "application/json"
        And I add "Authorization" header equal to "Basic cGVlajp0ZXN0"
        And I send a POST request on "/users/peej/repos/test/branches/master/commits/{sha}/revert" with sha 3
        Then the response status code should be 201
        And I remember the commit sha from the location header
        Given I send a GET request on "/users/peej/repos/test/branches/master/commits/{sha}" with sha 6
        Then response property "message" should be "Undo change {sha}" with sha 3

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
        Then response property "message" should be "Custom commit message"

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
        Then response property "message" should be "Custom commit message"