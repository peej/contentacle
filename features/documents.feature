Feature:
    As a user
    I should be able to see a branches documents

    Scenario: View a list of documents
        Given I send a GET request on "/users/peej/repos/test/branches/master/documents"
        Then response property "filename" should be ""
        And response property "_links->self->href" should be "/users/peej/repos/test/branches/master/documents"
        And response property "_embedded->documents->0->filename" should be "adir"
        And response property "_embedded->documents->0->_links->self->href" should be "/users/peej/repos/test/branches/master/documents/adir"
        And response property "_embedded->documents->1->filename" should be "afile.txt"
        And response property "_embedded->documents->1->_links->self->href" should be "/users/peej/repos/test/branches/master/documents/afile.txt"

    Scenario: View a list of documents within a directory
        Given I send a GET request on "/users/peej/repos/test/branches/master/documents/adir"
        Then response property "filename" should be "adir"
        And response property "_links->self->href" should be "/users/peej/repos/test/branches/master/documents/adir"
        And response property "_embedded->documents->0->filename" should be "emptyFile.txt"
        And response property "_embedded->documents->0->_links->self->href" should be "/users/peej/repos/test/branches/master/documents/adir/emptyFile.txt"

    Scenario: View a documents details
        Given I send a GET request on "/users/peej/repos/test/branches/master/documents/afile.txt"
        Then response property "_links->self->href" should be "/users/peej/repos/test/branches/master/documents/afile.txt"
        And response property "filename" should be "afile.txt"
        And response property "content" should be "Some content"
        And response property "_links->raw->href" should be "/users/peej/repos/test/branches/master/raw/afile.txt"
        And response property "_links->history->href" should be "/users/peej/repos/test/branches/master/history/afile.txt"

    Scenario: View a documents raw content
        Given I send a GET request on "/users/peej/repos/test/branches/master/raw/afile.txt"
        Then the response should contain "Some content"

    Scenario: View a documents history
        Given I send a GET request on "/users/peej/repos/test/branches/master/history/afile.txt"
        Then response property "_embedded->commits->0->_links->self->href" should be "/users/peej/repos/test/branches/master/commits/2c22b023d0979bcc768bc088063eb4a9a376db80"
        And response property "_embedded->commits->0->message" should be "Commit message"
        And response property "_embedded->commits->0->date" should be "1392493822"
        And response property "_embedded->commits->0->username" should be "Paul James"
        And response property "_embedded->commits->0->sha" should be "2c22b023d0979bcc768bc088063eb4a9a376db80"