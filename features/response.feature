Feature:
    As a user
    I should be able to recieve different API response formats

    Scenario: Default format
        Given I set the "Accept" header to "*/*"
        And I send a GET request on "/users"
        Then the "Content-Type" response header should be "text/yaml"
        And response property "_links->self->href" should be "/users"

    Scenario: YAML accept header
        Given I set the "Accept" header to "text/yaml"
        And I send a GET request on "/users"
        Then the "Content-Type" response header should be "text/yaml"
        And response property "_links->self->href" should be "/users.yaml"

    Scenario: Alternative YAML mimetype accept header
        Given I set the "accept" header to "application/yaml"
        And I send a GET request on "/users"
        Then the "Content-Type" response header should be "text/yaml"

    Scenario: YAML URL extension
        Given I send a GET request on "/users.yaml"
        Then the "Content-Type" response header should be "text/yaml"
        And response property "_links->self->href" should be "/users.yaml"
        And response property "_embedded->users->0->_links->self->href" should be "/users/peej.yaml"
        And response property "_embedded->users->0->username" should be "peej"
        And response property "_embedded->users->0->name" should be "Paul James"

    Scenario: JSON accept header
        Given I set the "accept" header to "application/json"
        And I send a GET request on "/users"
        Then the "Content-Type" response header should be "application/json"
        And response property "_links->self->href" should be "/users.json"

    Scenario: Alternative JSON mimetype accept header
        Given I set the "accept" header to "text/json"
        And I send a GET request on "/users"
        Then the "Content-Type" response header should be "application/json"
        
    Scenario: JSON URL extension
        Given I send a GET request on "/users.json"
        Then the "Content-Type" response header should be "application/json"
        And response property "_links->self->href" should be "/users.json"
        And response property "_embedded->users->0->_links->self->href" should be "/users/peej.json"
        And response property "_embedded->users->0->username" should be "peej"
        And response property "_embedded->users->0->name" should be "Paul James"
