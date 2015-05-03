Feature:
    As a user
    I should be able to see a branches documents

    Scenario: View a list of documents
        Given I send a GET request on "/users/peej/repos/test/branches/master/documents"
        Then the response status code should be 200
        And the content-type response header should be "application/hal+yaml"
        And response property "filename" should be ""
        And response property "_links->self->href" should be "/users/peej/repos/test/branches/master/documents"
        And response property "_embedded->cont:document->0->filename" should be "adir"
        And response property "_embedded->cont:document->0->_links->self->href" should be "/users/peej/repos/test/branches/master/documents/adir"
        And response property "_embedded->cont:document->1->filename" should be "afile.txt"
        And response property "_embedded->cont:document->1->_links->self->href" should be "/users/peej/repos/test/branches/master/documents/afile.txt"

    Scenario: View a list of documents within a directory
        Given I send a GET request on "/users/peej/repos/test/branches/master/documents/adir"
        Then the response status code should be 200
        And the content-type response header should be "application/hal+yaml"
        And response property "filename" should be "adir"
        And response property "dir" should be "true"
        And response property "path" should be "adir"
        And response property "_links->self->href" should be "/users/peej/repos/test/branches/master/documents/adir"
        And response property "_embedded->cont:document->0->filename" should be "and"
        And response property "_embedded->cont:document->0->_links->self->href" should be "/users/peej/repos/test/branches/master/documents/adir/and"
        And response property "_embedded->cont:document->1->filename" should be "emptyFile.txt"
        And response property "_embedded->cont:document->1->_links->self->href" should be "/users/peej/repos/test/branches/master/documents/adir/emptyFile.txt"

    Scenario: Have the correct HTTP methods
        Given I send an OPTIONS request to "/users/peej/repos/test/branches/master/documents"
        Then the "Allow" response header should be "OPTIONS,GET,PUT,DELETE"
        Given I send an OPTIONS request to "/users/peej/repos/test/branches/master/documents/afile.txt"
        Then the "Allow" response header should be "OPTIONS,GET,PUT,DELETE"

    Scenario: View a documents details
        Given I send a GET request on "/users/peej/repos/test/branches/master/documents/afile.txt"
        Then the response status code should be 200
        And the content-type response header should be "application/hal+yaml"
        And response property "_links->self->href" should be "/users/peej/repos/test/branches/master/documents/afile.txt"
        And response property "filename" should be "afile.txt"
        And response property "dir" should be "false"
        And response property "path" should be "afile.txt"
        And response property "content" should be "Some content"
        And response property "_links->cont:raw->href" should be "/users/peej/repos/test/branches/master/raw/afile.txt"
        And response property "_links->cont:history->href" should be "/users/peej/repos/test/branches/master/history/afile.txt"

    Scenario: View a documents raw content
        Given I send a GET request on "/users/peej/repos/test/branches/master/raw/afile.txt"
        Then the content-type response header should be "text/plain"
        And the response should contain "Some content"

    Scenario: View a documents history
        Given I send a GET request on "/users/peej/repos/test/branches/master/history/afile.txt"
        Then the response status code should be 200
        And the content-type response header should be "application/hal+yaml"
        And response property "_embedded->cont:commit->0->_links->self->href" should be "/users/peej/repos/test/branches/master/commits/{sha}" with sha 1
        And response property "_embedded->cont:commit->0->message" should be "1st commit"
        And response property "_embedded->cont:commit->0->authorname" should be "peej"
        And response property "_embedded->cont:commit->0->author" should be "Paul James"
        And response property "_embedded->cont:commit->0->sha" should be sha 1

    Scenario: Create a new document from raw content
        Given I add "Content-Type" header equal to "text/plain"
        And I add "Authorization" header equal to "Basic cGVlajp0ZXN0"
        When I send a PUT request to "/users/peej/repos/test/branches/master/documents/new.txt" with body:
            """
            New document
            """
        Then the response status code should be 201
        And the content-type response header should be "application/hal+yaml"
        And response property "filename" should be "new.txt"
        When I send a GET request to "/users/peej/repos/test/branches/master/documents/new.txt"
        Then the response status code should be 200
        And the content-type response header should be "application/hal+yaml"
        And response property "filename" should be "new.txt"
        And response property "dir" should be "false"
        And response property "path" should be "new.txt"
        And response property "content" should be "New document"
        And response property "authorname" should be "peej"
        When I send a GET request to "/users/peej/repos/test/branches/master/commits"
        Then response property "_embedded->cont:commit->0->message" should be "Create new.txt"
        And response property "_embedded->cont:commit->0->authorname" should be "peej"
    
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
        And the content-type response header should be "application/hal+yaml"
        And response property "filename" should be "new.txt"
        When I send a GET request to "/users/peej/repos/test/branches/master/documents/new.txt"
        Then the response status code should be 200
        And the content-type response header should be "application/hal+yaml"
        And response property "filename" should be "new.txt"
        And response property "dir" should be "false"
        And response property "path" should be "new.txt"
        And response property "content" should be "New document"
        And response property "authorname" should be "peej"
        When I send a GET request to "/users/peej/repos/test/branches/master/commits"
        Then response property "_embedded->cont:commit->0->message" should be "My commit message"
        And response property "_embedded->cont:commit->0->authorname" should be "peej"

    Scenario: Create a new document via the HTML form
        Given I set the "Accept" header to "text/html"
        And I add "Authorization" header equal to "Basic cGVlajp0ZXN0"
        And I am on "/users/peej/repos/test/branches/master/documents"
        And I follow the "create-form" relation
        Then the response status code should be 200
        When I fill in "filename" with "test"
        And I fill in "content" with "This is test content"
        And I fill in "message" with "Test commit message"
        And I press "Commit changes"
        Then the response status code should be 200
        And I should see "Test commit message"

    Scenario: Update a document raw
        Given I add "Content-Type" header equal to "text/plain"
        And I add "Authorization" header equal to "Basic cGVlajp0ZXN0"
        When I send a PUT request to "/users/peej/repos/test/branches/master/documents/afile.txt" with body:
            """
            Updated document
            """
        Then the response status code should be 200
        And the content-type response header should be "application/hal+yaml"
        And response property "filename" should be "afile.txt"
        And response property "dir" should be "false"
        And response property "path" should be "afile.txt"
        And response property "content" should be "Updated document"
        And response property "authorname" should be "peej"
        When I send a GET request to "/users/peej/repos/test/branches/master/commits"
        Then response property "_embedded->cont:commit->0->message" should be "Update afile.txt"
        And response property "_embedded->cont:commit->0->authorname" should be "peej"
    
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
        And the content-type response header should be "application/hal+yaml"
        And response property "filename" should be "afile.txt"
        And response property "dir" should be "false"
        And response property "path" should be "afile.txt"
        And response property "content" should be "Updated document"
        And response property "authorname" should be "peej"
        When I send a GET request to "/users/peej/repos/test/branches/master/commits"
        Then response property "_embedded->cont:commit->0->message" should be "My commit message"
        And response property "_embedded->cont:commit->0->authorname" should be "peej"
    
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
        And I follow the 1st "cont:documents" relation
        And I follow the 2nd "cont:document" relation
        Then the response status code should be 200
        And response property "filename" should be "afile.txt"

    Scenario: The cont:document link relation has documentation
        Given I send a GET request to "/users/peej/repos/test/branches/master/documents"
        When I uncurie the "cont:document" relation
        Then the response status code should be 200
        And response property "actions->get->description" should exist
        And response property "actions->get->request->method" should contain "get"
        And response property "actions->get->response->code" should contain "200 OK"
        And response property "actions->get->response->field->filename" should exist
        And response property "actions->get->response->field->path" should exist
        And response property "actions->get->response->field->type" should exist
        And response property "actions->get->response->field->sha" should exist
        And response property "actions->get->response->field->username" should exist
        And response property "actions->get->response->field->author" should exist
        And response property "actions->get->response->field->email" should exist
        And response property "actions->get->response->field->date" should exist
        And response property "actions->get->response->field->branch" should exist
        And response property "actions->get->response->field->commit" should exist
        And response property "actions->get->response->field->content" should exist
        And response property "actions->get->response->links->self" should exist
        And response property "actions->get->response->links->cont:doc" should exist
        And response property "actions->get->response->links->cont:user" should exist
        And response property "actions->get->response->links->cont:history" should exist
        And response property "actions->get->response->links->cont:raw" should exist
        And response property "actions->get->response->links->cont:commit" should exist
        And response property "actions->get->response->embeds->cont:document" should exist
        And response property "actions->get->response->provides" should contain "application/hal+yaml"
        And response property "actions->get->response->provides" should contain "application/hal+json"
        And response property "actions->createDocument->description" should exist
        And response property "actions->createDocument->request->method" should contain "put"
        And response property "actions->createDocument->request->accepts" should contain "application/yaml"
        And response property "actions->createDocument->request->accepts" should contain "application/json"
        And response property "actions->createDocument->request->accepts" should contain "*"
        And response property "actions->createDocument->request->field->message" should exist
        And response property "actions->createDocument->request->field->content" should exist
        And response property "actions->createDocument->response->code" should contain "200 OK"
        And response property "actions->createDocument->response->code" should contain "201 Created"
        And response property "actions->deleteDocument->description" should exist
        And response property "actions->deleteDocument->request->method" should contain "delete"
        And response property "actions->deleteDocument->request->accepts" should contain "application/yaml"
        And response property "actions->deleteDocument->request->accepts" should contain "application/json"
        And response property "actions->deleteDocument->request->field->message" should exist
        And response property "actions->deleteDocument->response->code" should contain "204 No content"

    Scenario: The cont:history link relation has documentation
        Given I send a GET request to "/users/peej/repos/test/branches/master/documents"
        When I uncurie the "cont:history" relation
        Then the response status code should be 200
        And response property "actions->get->description" should exist
        And response property "actions->get->request->method" should contain "get"
        And response property "actions->get->response->code" should contain "200 OK"
        And response property "actions->get->response->field->filename" should exist
        And response property "actions->get->response->field->path" should exist
        And response property "actions->get->response->links->self" should exist
        And response property "actions->get->response->links->cont:doc" should exist
        And response property "actions->get->response->links->cont:document" should exist
        And response property "actions->get->response->links->cont:raw" should exist
        And response property "actions->get->response->embeds->cont:commit" should exist
        And response property "actions->get->response->provides" should contain "application/hal+yaml"
        And response property "actions->get->response->provides" should contain "application/hal+json"

    Scenario: The cont:raw link relation has documentation
        Given I send a GET request to "/users/peej/repos/test/branches/master/documents/afile.txt"
        When I uncurie the "cont:raw" relation
        Then the response status code should be 200
        And response property "actions->get->description" should exist
        And response property "actions->get->request->method" should contain "get"
        And response property "actions->get->response->code" should contain "200 OK"