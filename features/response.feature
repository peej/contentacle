Feature:
    As a user
    I should be able to recieve different API response formats

    Scenario: Default format
        Given I send a GET request to "/users"
        Then the "Content-Type" response header should be "application/hal+yaml"
        And response property "_links->self->href" should be "/users"

    Scenario: YAML accept header
        Given I set the "Accept" header to "text/yaml"
        And I send a GET request to "/users"
        Then the "Content-Type" response header should be "application/hal+yaml"
        And response property "_links->self->href" should be "/users.yaml"

    Scenario: Alternative YAML mimetype accept header
        Given I set the "Accept" header to "application/yaml"
        And I send a GET request to "/users"
        Then the "Content-Type" response header should be "application/hal+yaml"

    Scenario: YAML URL extension
        Given I send a GET request to "/users.yaml"
        Then the "Content-Type" response header should be "application/hal+yaml"
        And response property "_links->self->href" should be "/users.yaml"
        And response property "_embedded->cont:user->1->_links->self->href" should be "/users/peej.yaml"
        And response property "_embedded->cont:user->1->username" should be "peej"
        And response property "_embedded->cont:user->1->name" should be "Paul James"

    Scenario: JSON accept header
        Given I set the "Accept" header to "application/json"
        And I send a GET request to "/users"
        Then the "Content-Type" response header should be "application/hal+json"
        And response property "_links->self->href" should be "/users.json"

    Scenario: Alternative JSON mimetype accept header
        Given I set the "Accept" header to "text/json"
        And I send a GET request to "/users"
        Then the "Content-Type" response header should be "application/hal+json"

    Scenario: JSON URL extension
        Given I send a GET request to "/users.json"
        Then the "Content-Type" response header should be "application/hal+json"
        And response property "_links->self->href" should be "/users.json"
        And response property "_embedded->cont:user->1->_links->self->href" should be "/users/peej.json"
        And response property "_embedded->cont:user->1->username" should be "peej"
        And response property "_embedded->cont:user->1->name" should be "Paul James"
