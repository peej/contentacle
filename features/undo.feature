Feature:
    As a user
    I should be able to undo a commit

    Scenario: Undo a commit
        Given I add "Authorization" header equal to "Basic cGVlajp0ZXN0"
        And I send a POST request on "/users/peej/repos/test/branches/master/commits/{sha}/undo" with sha 4
        Then the response status code should be 201
        And a "Location" response header should exist

    Scenario: Fail to undo a conflicting commit
        Given I add "Authorization" header equal to "Basic cGVlajp0ZXN0"
        And I send a POST request on "/users/peej/repos/test/branches/master/commits/{sha}/undo" with sha 1
        Then the response status code should be 409