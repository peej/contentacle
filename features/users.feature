Feature:
    As a user
    I should be able to see users

    Scenario: Link to itself
        Given I send a GET request on "/users.yaml"
        Then response property "_links->self->href" should be "/users.yaml"

    Scenario: Provide an add user form
        Given I send a GET request on "/users.yaml"
        Then response property "_links->add->method" should be "post"
        And response property "_links->add->content-type" should contain "contentacle/user+yaml"
        And response property "_links->add->content-type" should contain "contentacle/user+json"

    Scenario: View a list of users
        Given I send a GET request on "/users.yaml"
        Then response property "_embedded->users->0->username" should be "peej"
        And response property "_embedded->users->0->name" should be "Paul James"
        And response property "_embedded->users->0->_links->self->href" should be "/users/peej.yaml"

    Scenario: View a users details
        Given I send a GET request on "/users/peej.yaml"
        Then response property "username" should be "peej"
        And response property "name" should be "Paul James"
        And response property "email" should be "peej@localhost"
        And response property "_links->self->href" should be "/users/peej.yaml"
        And response property "_embedded->repos->0->name" should be "test"
        And response property "_embedded->repos->0->_links->self->href" should be "/users/peej/repos/test.yaml"
