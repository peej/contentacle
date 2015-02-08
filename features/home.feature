Feature:
    As a user
    I should be able to see the homepage

    Scenario: Link to itself
        When I send a GET request to "/"
        Then response property "_links->self->href" should be "/"
        And the "Content-Type" response header should be "application/hal+yaml"

    Scenario: Link to itself with explicit content type
        Given I set the "Accept" header to "text/json"
        When I send a GET request to "/"
        Then response property "_links->self->href" should be "/.json"
        And the "Content-Type" response header should be "application/hal+json"

    Scenario: Link to itself with explicit content type
        When I send a GET request to "/.yaml"
        Then response property "_links->self->href" should be "/.yaml"
        And the "Content-Type" response header should be "application/hal+yaml"

    Scenario: Link to user list
        When I send a GET request to "/"
        Then response property "_links->cont:users->href" should be "/users"

    Scenario: Have the correct HTTP methods
        Given I send an OPTIONS request to "/"
        Then the "Allow" response header should be "OPTIONS,GET"

    Scenario: Show HTML homepage
        Given I set the "Accept" header to "text/html"
        When I send a GET request to "/"
        Then I should see a "body" element
        And the "Content-Type" response header should be "text/html"

    Scenario: Show HTML homepage with explicit content type
        When I send a GET request to "/.html"
        Then I should see a "body" element
        And the "Content-Type" response header should be "text/html"