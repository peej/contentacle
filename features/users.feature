Feature:
    As a user
    I should be able to see users

    Scenario: Link to itself
        When I send a GET request to "/users.yaml"
        Then the content-type response header should be "application/hal+yaml"
        And response property "_links->self->href" should be "/users.yaml"

    Scenario: Link to documentation
        When I send a GET request to "/users.yaml"
        Then response property "_links->cont:doc->href" should be "/rels/users"

    Scenario: Have the correct HTTP methods
        Given I send an OPTIONS request to "/users"
        Then the "Allow" response header should be "OPTIONS,GET,POST"
        Given I send an OPTIONS request to "/users/peej"
        Then the "Allow" response header should be "OPTIONS,GET,PATCH,DELETE"

    Scenario: View a list of users
        When I send a GET request to "/users.yaml"
        Then the content-type response header should be "application/hal+yaml"
        And response property "_embedded->cont:user->1->username" should be "peej"
        And response property "_embedded->cont:user->1->name" should be "Paul James"
        And response property "_embedded->cont:user->1->_links->self->href" should be "/users/peej.yaml"

    Scenario: Search for users
        When I send a GET request to "/users.yaml?q=peej"
        Then response property "_embedded->cont:user->0->username" should be "peej"

    Scenario: View a lot of users
        Given I have an empty data store
        And I have users:
            | username | password | name     | email              |
            | alfa     | test     | Alfa     | alfa@gmail.com     |
            | bravo    | test     | Bravo    | bravo@gmail.com    |
            | charlie  | test     | Charlie  | charlie@gmail.com  |
            | delta    | test     | Delta    | delta@gmail.com    |
            | echo     | test     | Echo     | echo@gmail.com     |
            | foxtrot  | test     | Foxtrot  | foxtrot@gmail.com  |
            | golf     | test     | Golf     | golf@gmail.com     |
            | hotel    | test     | Hotel    | hotel@gmail.com    |
            | india    | test     | India    | india@gmail.com    |
            | juliett  | test     | Juliett  | juliett@gmail.com  |
            | kilo     | test     | Kilo     | kilo@gmail.com     |
            | lima     | test     | Lima     | lima@gmail.com     |
            | mike     | test     | Mike     | mike@gmail.com     |
            | november | test     | November | november@gmail.com |
            | oscar    | test     | Oscar    | oscar@gmail.com    |
            | papa     | test     | Papa     | papa@gmail.com     |
            | quebec   | test     | Quebec   | quebec@gmail.com   |
            | romeo    | test     | Romeo    | romeo@gmail.com    |
            | sierra   | test     | Sierra   | sierra@gmail.com   |
            | tango    | test     | Tango    | tango@gmail.com    |
            | uniform  | test     | Uniform  | uniform@gmail.com  |
            | victor   | test     | Victor   | victor@gmail.com   |
            | whiskey  | test     | Whiskey  | whiskey@gmail.com  |
            | x-ray    | test     | X-ray    | x-ray@gmail.com    |
            | yankee   | test     | Yankee   | yankee@gmail.com   |
            | zulu     | test     | Zulu     | zulu@gmail.com     |
        When I send a GET request to "/users.yaml"
        Then response property "_embedded->cont:user->0->username" should be "alfa"
        And response property "_embedded->cont:user->19->username" should be "tango"
        And response property "_embedded->cont:user->20" should not exist
        And response property "_links->prev" should not exist
        And response property "_links->next->href" should be "/users?page=2"

    Scenario: View a users details
        When I send a GET request to "/users/peej.yaml"
        Then the response status code should be 200
        And the content-type response header should be "application/hal+yaml"
        And response property "username" should be "peej"
        And response property "name" should be "Paul James"
        And response property "email" should be "paul@peej.co.uk"
        And response property "_links->self->href" should be "/users/peej.yaml"
        And response property "_links->cont:doc->href" should be "/rels/user"
        And response property "_embedded->cont:repo->1->name" should be "test"
        And response property "_embedded->cont:repo->1->_links->self->href" should be "/users/peej/repos/test.yaml"

    Scenario: User has a default email address if not created with one
        When I send a GET request to "/users/empty.yaml"
        Then response property "email" should be "empty@localhost"

    Scenario: Receive a 404 for a non-existant user
        When I send a GET request to "/users/missing"
        Then the response status code should be 404
        And the directory "missing" should not exist

    Scenario: Create a user
        Given I add "Content-Type" header equal to "application/json"
        When I send a POST request to "/users" with body:
            """
            {
                "username": "test1",
                "name": "Behat Tester",
                "password": "test1"
            }
            """
        Then the response status code should be 201
        And the header "Location" should be equal to "/users/test1"
        When I send a GET request to "/users/test1"
        Then the response status code should be 200
        And the content-type response header should be "application/hal+yaml"
        And response property "username" should be "test1"
        And response property "password" should be ""
        And response property "email" should be "test1@localhost"

    Scenario: Try to create an invalid user
        Given I add "Content-Type" header equal to "application/json"
        When I send a POST request to "/users" with body:
            """
            {
                "username": "***",
                "password": "test1"
            }
            """
        Then the response status code should be 400
        And the content-type response header should be "application/hal+yaml"
        And response property "_embedded->cont:error->0->logref" should be "username"
        And response property "_embedded->cont:error->1->logref" should be "name"

    Scenario: Create a user with an HTML form
        Given I add "Content-Type" header equal to "application/x-www-form-urlencoded"
        And I add "Accept" header equal to "text/html"
        When I send a POST request to "/users" with body:
            """
            username=test1&name=Behat Tester&password=test1
            """
        Then the response status code should be 303
        And the header "Location" should be equal to "/users/test1.html"

    Scenario: Patch a user
        Given I add "Content-Type" header equal to "application/json-patch+json"
        And I add "Authorization" header equal to "Basic cGVlajp0ZXN0"
        When I send a PATCH request to "/users/peej" with body:
            """
            [{
                "op": "replace",
                "path": "name",
                "value": "PJ"
            }]
            """
        Then the response status code should be 200
        And the content-type response header should be "application/hal+yaml"
        And response property "username" should be "peej"
        And response property "name" should be "PJ"
        When I send a GET request to "/users/peej"
        And response property "username" should be "peej"
        And response property "name" should be "PJ"

    Scenario: Delete a user
        Given I add "Authorization" header equal to "Basic cGVlajp0ZXN0"
        When I send a DELETE request to "/users/peej"
        Then the response status code should be 204
        When I send a GET request to "/users/peej"
        Then the response status code should be 404

    Scenario: Fail to provide correct auth credentials
        Given I add "Authorization" header equal to "Basic wrong"
        When I send a DELETE request to "/users/peej"
        Then the response status code should be 401

    Scenario: Navigate to a user
        Given I am on the homepage
        When I follow the "cont:users" relation
        And I follow the 2nd "cont:user" relation
        Then the response status code should be 200
        And response property "username" should be "peej"
        And response property "name" should be "Paul James"

    Scenario: The cont:users link relation has documentation
        Given I send a GET request to "/users"
        When I uncurie the "cont:users" relation
        Then the response status code should be 200
        And response property "actions->get->description" should exist
        And response property "actions->get->request->method" should contain "get"
        And response property "actions->get->response->code" should contain "200 OK"
        And response property "actions->get->response->field->username" should exist
        And response property "actions->get->response->field->password" should exist
        And response property "actions->get->response->field->name" should exist
        And response property "actions->get->response->field->email" should exist
        And response property "actions->get->response->links->self" should exist
        And response property "actions->get->response->links->cont:doc" should exist
        And response property "actions->get->response->embeds->cont:user" should exist
        And response property "actions->get->response->provides" should contain "application/hal+yaml"
        And response property "actions->get->response->provides" should contain "application/hal+json"
        And response property "actions->get->response->provides" should contain "text/html"
        And response property "actions->createUser->description" should exist
        And response property "actions->createUser->request->method" should contain "post"
        And response property "actions->createUser->request->accepts" should contain "application/yaml"
        And response property "actions->createUser->request->accepts" should contain "application/json"
        And response property "actions->createUser->request->accepts" should contain "application/x-www-form-urlencoded"
        And response property "actions->createUser->request->field->username" should exist
        And response property "actions->createUser->request->field->password" should exist
        And response property "actions->createUser->request->field->name" should exist
        And response property "actions->createUser->request->field->email" should exist
        And response property "actions->createUser->response->code" should contain "201 Created"
        And response property "actions->createUser->response->code" should contain "400 Bad request"
        And response property "actions->createUser->response->header->Location" should exist
        And response property "actions->createUser->response->embeds->cont:error" should exist
        And response property "actions->createUser->response->provides" should contain "application/hal+yaml"
        And response property "actions->createUser->response->provides" should contain "application/hal+json"
        And response property "actions->createUser->response->provides" should contain "text/html"

    Scenario: The cont:user link relation has documentation
        Given I send a GET request to "/users/peej"
        When I uncurie the "cont:user" relation
        Then the response status code should be 200
        And response property "actions->get->description" should exist
        And response property "actions->get->request->method" should contain "get"
        And response property "actions->get->response->code" should contain "200 OK"
        And response property "actions->get->response->field->username" should exist
        And response property "actions->get->response->field->password" should exist
        And response property "actions->get->response->field->name" should exist
        And response property "actions->get->response->field->email" should exist
        And response property "actions->get->response->links->self" should exist
        And response property "actions->get->response->links->cont:doc" should exist
        And response property "actions->get->response->links->cont:repos" should exist
        And response property "actions->get->response->embeds->cont:repo" should exist
        And response property "actions->get->response->provides" should contain "application/hal+yaml"
        And response property "actions->get->response->provides" should contain "application/hal+json"
        And response property "actions->updateUser->description" should exist
        And response property "actions->updateUser->request->method" should contain "patch"
        And response property "actions->updateUser->request->accepts" should contain "application/json-patch+json"
        And response property "actions->updateUser->request->accepts" should contain "application/json-patch+yaml"
        And response property "actions->updateUser->request->field->username" should exist
        And response property "actions->updateUser->request->field->password" should exist
        And response property "actions->updateUser->request->field->name" should exist
        And response property "actions->updateUser->request->field->email" should exist
        And response property "actions->updateUser->response->code" should contain "200 OK"
        And response property "actions->updateUser->response->links->self" should exist
        And response property "actions->updateUser->response->links->cont:doc" should exist
        And response property "actions->updateUser->response->links->cont:repos" should exist
        And response property "actions->updateUser->response->embeds->cont:repo" should exist
        And response property "actions->updateUser->response->provides" should contain "application/hal+yaml"
        And response property "actions->updateUser->response->provides" should contain "application/hal+json"
        And response property "actions->deleteUser->description" should exist
        And response property "actions->deleteUser->request->method" should contain "delete"
        And response property "actions->deleteUser->response->code" should contain "204 No content"