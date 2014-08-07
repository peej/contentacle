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