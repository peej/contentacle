Feature:
    As a user
    I should be able to see a branches documents

    Scenario: View a list of documents
        Given I send a GET request on "/users/peej/repos/test/branches/master/documents"
        Then the header "Content-Type" should be equal to "contentacle/documents+yaml"
        And response property "filename" should be ""
        And response property "_links->self->href" should be "/users/peej/repos/test/branches/master/documents"
        And response property "_embedded->documents->0->filename" should be "adir"
        And response property "_embedded->documents->0->_links->self->href" should be "/users/peej/repos/test/branches/master/documents/adir"
        And response property "_embedded->documents->1->filename" should be "afile.txt"
        And response property "_embedded->documents->1->_links->self->href" should be "/users/peej/repos/test/branches/master/documents/afile.txt"

    Scenario: View a list of documents within a directory
        Given I send a GET request on "/users/peej/repos/test/branches/master/documents/adir"
        Then the header "Content-Type" should be equal to "contentacle/documents+yaml"
        And response property "filename" should be "adir"
        And response property "_links->self->href" should be "/users/peej/repos/test/branches/master/documents/adir"
        And response property "_embedded->documents->0->filename" should be "emptyFile.txt"
        And response property "_embedded->documents->0->_links->self->href" should be "/users/peej/repos/test/branches/master/documents/adir/emptyFile.txt"

    Scenario: Provide a create document form
        When I send a GET request to "/users/peej/repos/test/branches/master/documents"
        Then response property "_links->cont:add-document->method" should be "put"
        And response property "_links->cont:add-document->href" should be "/users/peej/repos/test/branches/master/documents/{path}"
        And response property "_links->cont:add-document->templated" should be "true"
        And response property "_links->cont:add-document->content-type" should contain "contentacle/document+json"
        And response property "_links->cont:add-document->content-type" should contain "contentacle/document+yaml"
        And response property "_links->cont:add-document->content-type" should contain "*/*"

    Scenario: View a documents details
        Given I send a GET request on "/users/peej/repos/test/branches/master/documents/afile.txt"
        Then the header "Content-Type" should be equal to "contentacle/document+yaml"
        And response property "_links->self->href" should be "/users/peej/repos/test/branches/master/documents/afile.txt"
        And response property "filename" should be "afile.txt"
        And response property "content" should be "Some content"
        And response property "_links->cont:raw->href" should be "/users/peej/repos/test/branches/master/raw/afile.txt"
        And response property "_links->cont:history->href" should be "/users/peej/repos/test/branches/master/history/afile.txt"

    Scenario: Provide an update document form
        When I send a GET request to "/users/peej/repos/test/branches/master/documents/afile.txt"
        Then response property "_links->cont:update-document->method" should be "patch"
        And response property "_links->cont:update-document->content-type" should contain "application/json-patch+yaml"
        And response property "_links->cont:update-document->content-type" should contain "application/json-patch+json"

    Scenario: Provide an edit document form
        When I send a GET request to "/users/peej/repos/test/branches/master/documents/afile.txt"
        Then response property "_links->cont:edit-document->method" should be "put"
        And response property "_links->cont:edit-document->href" should be "/users/peej/repos/test/branches/master/raw/afile.txt"
        And response property "_links->cont:edit-document->content-type" should be "*/*"

    Scenario: Provide a delete document form
        When I send a GET request to "/users/peej/repos/test/branches/master/documents/afile.txt"
        Then response property "_links->cont:delete-document->method" should be "delete"

    Scenario: View a documents raw content
        Given I send a GET request on "/users/peej/repos/test/branches/master/raw/afile.txt"
        Then the header "Content-Type" should be equal to "text/plain"
        And the response should contain "Some content"

    Scenario: View a documents history
        Given I send a GET request on "/users/peej/repos/test/branches/master/history/afile.txt"
        Then the header "Content-Type" should be equal to "contentacle/history+yaml"
        And response property "_embedded->commits->0->_links->self->href" should be "/users/peej/repos/test/branches/master/commits/{sha}" with sha 3
        And response property "_embedded->commits->0->message" should be "Commit message"
        And response property "_embedded->commits->0->username" should be "peej"
        And response property "_embedded->commits->0->author" should be "Paul James"
        And response property "_embedded->commits->0->sha" should be sha 3

    Scenario: Create a new document from raw content
        Given I add "Content-Type" header equal to "text/plain"
        And I add "Authorization" header equal to "Basic cGVlajp0ZXN0"
        When I send a PUT request to "/users/peej/repos/test/branches/master/documents/new.txt" with body:
            """
            New document
            """
        Then the response status code should be 201
        And the header "Content-Type" should be equal to "contentacle/document+yaml"
        And response property "filename" should be "new.txt"
        When I send a GET request to "/users/peej/repos/test/branches/master/documents/new.txt"
        Then the response status code should be 200
        And the header "Content-Type" should be equal to "contentacle/document+yaml"
        And response property "filename" should be "new.txt"
        And response property "type" should be "file"
        And response property "content" should be "New document"
        And response property "username" should be "peej"
        When I send a GET request to "/users/peej/repos/test/branches/master/commits"
        Then response property "_embedded->commits->0->message" should be "Create new.txt"
        And response property "_embedded->commits->0->username" should be "peej"
    
    Scenario: Create a new document from JSON document
        Given I add "Content-Type" header equal to "contentacle/document+json"
        And I add "Authorization" header equal to "Basic cGVlajp0ZXN0"
        When I send a PUT request to "/users/peej/repos/test/branches/master/documents/new.txt" with body:
            """
            {
                "content": "New document",
                "message": "My commit message"
            }
            """
        Then the response status code should be 201
        And the header "Content-Type" should be equal to "contentacle/document+yaml"
        And response property "filename" should be "new.txt"
        When I send a GET request to "/users/peej/repos/test/branches/master/documents/new.txt"
        Then the response status code should be 200
        And the header "Content-Type" should be equal to "contentacle/document+yaml"
        And response property "filename" should be "new.txt"
        And response property "type" should be "file"
        And response property "content" should be "New document"
        And response property "username" should be "peej"
        When I send a GET request to "/users/peej/repos/test/branches/master/commits"
        Then response property "_embedded->commits->0->message" should be "My commit message"
        And response property "_embedded->commits->0->username" should be "peej"

    Scenario: Update a document raw
        Given I add "Content-Type" header equal to "text/plain"
        And I add "Authorization" header equal to "Basic cGVlajp0ZXN0"
        When I send a PUT request to "/users/peej/repos/test/branches/master/documents/afile.txt" with body:
            """
            Updated document
            """
        Then the response status code should be 200
        And the header "Content-Type" should be equal to "contentacle/document+yaml"
        And response property "filename" should be "afile.txt"
        And response property "type" should be "file"
        And response property "content" should be "Updated document"
        And response property "username" should be "peej"
        When I send a GET request to "/users/peej/repos/test/branches/master/commits"
        Then response property "_embedded->commits->0->message" should be "Update afile.txt"
        And response property "_embedded->commits->0->username" should be "peej"
    
    Scenario: Update a document by JSON
        Given I add "Content-Type" header equal to "contentacle/document+json"
        And I add "Authorization" header equal to "Basic cGVlajp0ZXN0"
        When I send a PUT request to "/users/peej/repos/test/branches/master/documents/afile.txt" with body:
            """
            {
                "content": "Updated document",
                "message": "My commit message"
            }
            """
        Then the response status code should be 200
        And the header "Content-Type" should be equal to "contentacle/document+yaml"
        And response property "filename" should be "afile.txt"
        And response property "type" should be "file"
        And response property "content" should be "Updated document"
        And response property "username" should be "peej"
        When I send a GET request to "/users/peej/repos/test/branches/master/commits"
        Then response property "_embedded->commits->0->message" should be "My commit message"
        And response property "_embedded->commits->0->username" should be "peej"
    
    Scenario: Delete a document
        Given I add "Authorization" header equal to "Basic cGVlajp0ZXN0"
        When I send a DELETE request to "/users/peej/repos/test/branches/master/documents/afile.txt"
        Then the response status code should be 204
        When I send a GET request to "/users/peej/repos/test/branches/master/documents/afile.txt"
        Then the response status code should be 404
        When I send a GET request to "/users/peej/repos/test/branches/master/commits"
        Then response property "_embedded->commits->0->message" should be "Delete afile.txt"
