Feature:
    As an API client
    I should be able to navigate the API

    Scenario: Navigate to a user
        Given I am on the homepage
        And I follow the "cont:users" relation
        And I follow the 2nd "cont:user" relation
        Then response property "username" should be "peej"

    Scenario: Navigate to a repo
        Given I am on the homepage
        And I follow the "cont:users" relation
        And I follow the 2nd "cont:user" relation
        And I follow the "cont:repos" relation
        And I follow the 2nd "cont:repo" relation
        Then response property "username" should be "peej"
        And response property "name" should be "test"

    Scenario: Navigate to a branch
        Given I am on the homepage
        And I follow the "cont:users" relation
        And I follow the 2nd "cont:user" relation
        And I follow the "cont:repos" relation
        And I follow the 2nd "cont:repo" relation
        And I follow the "cont:branches" relation
        And I follow the 2nd "cont:branch" relation
        Then response property "username" should be "peej"
        And response property "repo" should be "test"
        And response property "branch" should be "master"

    Scenario: Navigate to a document
        Given I am on the homepage
        And I follow the "cont:users" relation
        And I follow the 2nd "cont:user" relation
        And I follow the "cont:repos" relation
        And I follow the 2nd "cont:repo" relation
        And I follow the "cont:branches" relation
        And I follow the 2nd "cont:branch" relation
        And I follow the "cont:documents" relation
        And I follow the 2nd "cont:document" relation
        Then response property "path" should be "afile.txt"
        And response property "username" should be "peej"
        And response property "repo" should be "test"
        And response property "branch" should be "master"

    Scenario: Navigate to a documents commit
        Given I am on the homepage
        And I follow the "cont:users" relation
        And I follow the 2nd "cont:user" relation
        And I follow the "cont:repos" relation
        And I follow the 2nd "cont:repo" relation
        And I follow the "cont:branches" relation
        And I follow the 2nd "cont:branch" relation
        And I follow the "cont:documents" relation
        And I follow the 2nd "cont:document" relation
        And I follow the 1st "cont:commit" relation
        Then response property "message" should be "1st commit"
        And response property "files" should contain "afile.txt"
        And response property "username" should be "peej"
        And response property "repo" should be "test"
        And response property "branch" should be "master"

    Scenario: Navigate to a commit
        Given I am on the homepage
        And I follow the "cont:users" relation
        And I follow the 2nd "cont:user" relation
        And I follow the "cont:repos" relation
        And I follow the 2nd "cont:repo" relation
        And I follow the "cont:branches" relation
        And I follow the 2nd "cont:branch" relation
        And I follow the "cont:commits" relation
        And I follow the 1st "cont:commit" relation
        Then response property "message" should be "Adding some Markdown."
        And response property "files" should contain "example.md"
        And response property "username" should be "peej"
        And response property "repo" should be "test"
        And response property "branch" should be "master"

    Scenario: Navigate to a documents history
        Given I am on the homepage
        And I follow the "cont:users" relation
        And I follow the 2nd "cont:user" relation
        And I follow the "cont:repos" relation
        And I follow the 2nd "cont:repo" relation
        And I follow the "cont:branches" relation
        And I follow the 2nd "cont:branch" relation
        And I follow the "cont:documents" relation
        And I follow the 2nd "cont:document" relation
        And I follow the 1st "cont:history" relation
        And I follow the 1st "cont:commit" relation
        Then response property "message" should be "1st commit"
        And response property "files" should contain "afile.txt"
        And response property "username" should be "peej"
        And response property "repo" should be "test"
        And response property "branch" should be "master"
