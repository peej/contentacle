Feature:
    As a user
    I should be able to see a branches commits

    Scenario: View a list of commits
        Given I send a GET request on "/users/peej/repos/test/branches/master/commits"
        Then response property "_links->self->href" should be "/users/peej/repos/test/branches/master/commits"
        And response property "_links->cont:commit->method" should be "post"
        And response property "_links->cont:commit->content-type" should contain "contentacle/commit+yaml"
        And response property "_embedded->commits->0->sha" should be "197e9ace806c2178e00575406e0a131d45c13698"
        And response property "_embedded->commits->1->sha" should be "2d5ab33ec4ffc1ee47eddce12aed4d0ccea8a086"
        And response property "_embedded->commits->2->sha" should be "2c22b023d0979bcc768bc088063eb4a9a376db80"

    Scenario: View a commit
        Given I send a GET request on "/users/peej/repos/test/branches/master/commits/197e9ace806c2178e00575406e0a131d45c13698"
        Then response property "_links->self->href" should be "/users/peej/repos/test/branches/master/commits/197e9ace806c2178e00575406e0a131d45c13698"
        And response property "sha" should be "197e9ace806c2178e00575406e0a131d45c13698"
        And response property "email" should be "paul@peej.co.uk"
        And response property "username" should be "peej"
        And response property "author" should be "Paul James"
        And response property "files" should contain "contentacle.yaml"