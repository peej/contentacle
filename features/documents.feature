Feature:
    As a user
    I should be able to see a branches documents

    Scenario: View a list of documents
        Given I send a GET request on "/users/peej/repos/test/branches/master/documents"
        Then the response status code should be 200
        And the header "Content-Type" should be equal to "application/hal+yaml"
        And response property "filename" should be ""
        And response property "_links->self->href" should be "/users/peej/repos/test/branches/master/documents"
        And response property "_embedded->cont:document->0->filename" should be "adir"
        And response property "_embedded->cont:document->0->_links->self->href" should be "/users/peej/repos/test/branches/master/documents/adir"
        And response property "_embedded->cont:document->1->filename" should be "afile.txt"
        And response property "_embedded->cont:document->1->_links->self->href" should be "/users/peej/repos/test/branches/master/documents/afile.txt"

    Scenario: View a list of documents within a directory
        Given I send a GET request on "/users/peej/repos/test/branches/master/documents/adir"
        Then the response status code should be 200
        And the header "Content-Type" should be equal to "application/hal+yaml"
        And response property "filename" should be "adir"
        And response property "_links->self->href" should be "/users/peej/repos/test/branches/master/documents/adir"
        And response property "_embedded->cont:document->0->filename" should be "emptyFile.txt"
        And response property "_embedded->cont:document->0->_links->self->href" should be "/users/peej/repos/test/branches/master/documents/adir/emptyFile.txt"

    Scenario: Have the correct HTTP methods
        Given I send an OPTIONS request to "/users/peej/repos/test/branches/master/documents"
        Then the "Allow" response header should be "OPTIONS,GET,PUT,DELETE"
        Given I send an OPTIONS request to "/users/peej/repos/test/branches/master/documents/afile.txt"
        Then the "Allow" response header should be "OPTIONS,GET,PUT,DELETE"

    Scenario: View a documents details
        Given I send a GET request on "/users/peej/repos/test/branches/master/documents/afile.txt"
        Then the response status code should be 200
        And the header "Content-Type" should be equal to "application/hal+yaml"
        And response property "_links->self->href" should be "/users/peej/repos/test/branches/master/documents/afile.txt"
        And response property "filename" should be "afile.txt"
        And response property "content" should be "Some content"
        And response property "_links->cont:raw->href" should be "/users/peej/repos/test/branches/master/raw/afile.txt"
        And response property "_links->cont:history->href" should be "/users/peej/repos/test/branches/master/history/afile.txt"

    Scenario: View a documents raw content
        Given I send a GET request on "/users/peej/repos/test/branches/master/raw/afile.txt"
        Then the header "Content-Type" should be equal to "text/plain"
        And the response should contain "Some content"

    Scenario: View a documents history
        Given I send a GET request on "/users/peej/repos/test/branches/master/history/afile.txt"
        Then the response status code should be 200
        And the header "Content-Type" should be equal to "application/hal+yaml"
        And response property "_embedded->cont:commit->0->_links->self->href" should be "/users/peej/repos/test/branches/master/commits/{sha}" with sha 3
        And response property "_embedded->cont:commit->0->message" should be "1st commit"
        And response property "_embedded->cont:commit->0->username" should be "peej"
        And response property "_embedded->cont:commit->0->author" should be "Paul James"
        And response property "_embedded->cont:commit->0->sha" should be sha 3

    Scenario: Create a new document from raw content
        Given I add "Content-Type" header equal to "text/plain"
        And I add "Authorization" header equal to "Basic cGVlajp0ZXN0"
        When I send a PUT request to "/users/peej/repos/test/branches/master/documents/new.txt" with body:
            """
            New document
            """
        Then the response status code should be 201
        And the header "Content-Type" should be equal to "application/hal+yaml"
        And response property "filename" should be "new.txt"
        When I send a GET request to "/users/peej/repos/test/branches/master/documents/new.txt"
        Then the response status code should be 200
        And the header "Content-Type" should be equal to "application/hal+yaml"
        And response property "filename" should be "new.txt"
        And response property "type" should be "file"
        And response property "content" should be "New document"
        And response property "username" should be "peej"
        When I send a GET request to "/users/peej/repos/test/branches/master/commits"
        Then response property "_embedded->cont:commit->0->message" should be "Create new.txt"
        And response property "_embedded->cont:commit->0->username" should be "peej"
    
    Scenario: Create a new document from JSON document
        Given I add "Content-Type" header equal to "application/json"
        And I add "Authorization" header equal to "Basic cGVlajp0ZXN0"
        When I send a PUT request to "/users/peej/repos/test/branches/master/documents/new.txt" with body:
            """
            {
                "content": "New document",
                "message": "My commit message"
            }
            """
        Then the response status code should be 201
        And the header "Content-Type" should be equal to "application/hal+yaml"
        And response property "filename" should be "new.txt"
        When I send a GET request to "/users/peej/repos/test/branches/master/documents/new.txt"
        Then the response status code should be 200
        And the header "Content-Type" should be equal to "application/hal+yaml"
        And response property "filename" should be "new.txt"
        And response property "type" should be "file"
        And response property "content" should be "New document"
        And response property "username" should be "peej"
        When I send a GET request to "/users/peej/repos/test/branches/master/commits"
        Then response property "_embedded->cont:commit->0->message" should be "My commit message"
        And response property "_embedded->cont:commit->0->username" should be "peej"

    Scenario: Update a document raw
        Given I add "Content-Type" header equal to "text/plain"
        And I add "Authorization" header equal to "Basic cGVlajp0ZXN0"
        When I send a PUT request to "/users/peej/repos/test/branches/master/documents/afile.txt" with body:
            """
            Updated document
            """
        Then the response status code should be 200
        And the header "Content-Type" should be equal to "application/hal+yaml"
        And response property "filename" should be "afile.txt"
        And response property "type" should be "file"
        And response property "content" should be "Updated document"
        And response property "username" should be "peej"
        When I send a GET request to "/users/peej/repos/test/branches/master/commits"
        Then response property "_embedded->cont:commit->0->message" should be "Update afile.txt"
        And response property "_embedded->cont:commit->0->username" should be "peej"
    
    Scenario: Update a document by JSON
        Given I add "Content-Type" header equal to "application/json"
        And I add "Authorization" header equal to "Basic cGVlajp0ZXN0"
        When I send a PUT request to "/users/peej/repos/test/branches/master/documents/afile.txt" with body:
            """
            {
                "content": "Updated document",
                "message": "My commit message"
            }
            """
        Then the response status code should be 200
        And the header "Content-Type" should be equal to "application/hal+yaml"
        And response property "filename" should be "afile.txt"
        And response property "type" should be "file"
        And response property "content" should be "Updated document"
        And response property "username" should be "peej"
        When I send a GET request to "/users/peej/repos/test/branches/master/commits"
        Then response property "_embedded->cont:commit->0->message" should be "My commit message"
        And response property "_embedded->cont:commit->0->username" should be "peej"
    
    Scenario: Delete a document
        Given I add "Authorization" header equal to "Basic cGVlajp0ZXN0"
        When I send a DELETE request to "/users/peej/repos/test/branches/master/documents/afile.txt"
        Then the response status code should be 204
        When I send a GET request to "/users/peej/repos/test/branches/master/documents/afile.txt"
        Then the response status code should be 404
        When I send a GET request to "/users/peej/repos/test/branches/master/commits"
        Then response property "_embedded->cont:commit->0->message" should be "Delete afile.txt"

    Scenario: Navigate to a document
        Given I am on the homepage
        When I follow the "cont:users" relation
        And I follow the 2nd "cont:user" relation
        And I follow the 2nd "cont:repo" relation
        And I follow the 2nd "cont:branch" relation
        And I follow the 1st "cont:document" relation
        And I follow the 2nd "cont:document" relation
        Then the response status code should be 200
        And response property "filename" should be "afile.txt"

    Scenario: The cont:document link relation has documentation
        Given I send a GET request to "/users/peej/repos/test/branches/master/documents"
        When I uncurie the "cont:document" relation
        Then the response status code should be 200
        And response property "get->description" should exist
        And response property "get->response" should contain "200 OK"
        And response property "get->field->filename" should exist
        And response property "get->field->path" should exist
        And response property "get->field->type" should exist
        And response property "get->field->sha" should exist
        And response property "get->field->username" should exist
        And response property "get->field->author" should exist
        And response property "get->field->email" should exist
        And response property "get->field->date" should exist
        And response property "get->field->branch" should exist
        And response property "get->field->commit" should exist
        And response property "get->field->content" should exist
        And response property "get->links->self" should exist
        And response property "get->links->cont:doc" should exist
        And response property "get->links->cont:user" should exist
        And response property "get->links->cont:history" should exist
        And response property "get->links->cont:raw" should exist
        And response property "get->links->cont:commit" should exist
        And response property "get->embeds->cont:document" should exist
        And response property "get->provides" should contain "application/hal+yaml"
        And response property "get->provides" should contain "application/hal+json"
        And response property "put->description" should exist
        And response property "put->accepts" should contain "application/yaml"
        And response property "put->accepts" should contain "application/json"
        And response property "put->accepts" should contain "*"
        And response property "put->field->message" should exist
        And response property "put->field->content" should exist
        And response property "put->response" should contain "200 OK"
        And response property "put->response" should contain "201 Created"
        And response property "delete->description" should exist
        And response property "delete->accepts" should contain "application/yaml"
        And response property "delete->accepts" should contain "application/json"
        And response property "delete->field->message" should exist
        And response property "delete->response" should contain "204 No content"

    Scenario: The cont:history link relation has documentation
        Given I send a GET request to "/users/peej/repos/test/branches/master/documents"
        When I uncurie the "cont:history" relation
        Then the response status code should be 200
        And response property "get->description" should exist
        And response property "get->response" should contain "200 OK"
        And response property "get->field->filename" should exist
        And response property "get->field->path" should exist
        And response property "get->links->self" should exist
        And response property "get->links->cont:doc" should exist
        And response property "get->links->cont:document" should exist
        And response property "get->links->cont:raw" should exist
        And response property "get->embeds->cont:commit" should exist
        And response property "get->provides" should contain "application/hal+yaml"
        And response property "get->provides" should contain "application/hal+json"

    Scenario: The cont:raw link relation has documentation
        Given I send a GET request to "/users/peej/repos/test/branches/master/documents/afile.txt"
        When I uncurie the "cont:raw" relation
        Then the response status code should be 200
        And response property "get->description" should exist
        And response property "get->response" should contain "200 OK"