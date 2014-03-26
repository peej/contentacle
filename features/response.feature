Feature:
    As a user
    I should be able to recieve different API response formats

    Scenario: YAML
        Given I send a GET request on "/users.yml"
        Then the response should contain "url: /users/peej"
        And the response should contain "username: peej"
        And the response should contain "name: Paul James"

    Scenario: JSON
        Given I send a GET request on "/users.json"
        Then print last response
        Then the response should contain "\"url\": \"\/users\/peej\""
        And the response should contain "\"username\": \"peej\""
        And the response should contain "\"name\": \"Paul James\""