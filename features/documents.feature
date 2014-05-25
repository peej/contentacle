Feature:
    As a user
    I should be able to see a branches documents

    Scenario: View a list of documents
        Given I send a GET request on "/users/peej/repos/test/branches/master/documents"
        Then response property "adir->url" should be "/users/peej/repos/test/branches/master/documents/adir"
        And response property "adir->filename" should be "adir"
        And response property "afile.txt->url" should be "/users/peej/repos/test/branches/master/documents/afile.txt"
        And response property "afile.txt->filename" should be "afile.txt"

    Scenario: View a list of documents within a directory
        Given I send a GET request on "/users/peej/repos/test/branches/master/documents/adir"
        Then response property "emptyFile.txt->url" should be "/users/peej/repos/test/branches/master/documents/adir/emptyFile.txt"
        And response property "emptyFile.txt->filename" should be "adir/emptyFile.txt"

    Scenario: View a documents details
        Given I send a GET request on "/users/peej/repos/test/branches/master/documents/afile.txt"
        Then response property "url" should be "/users/peej/repos/test/branches/master/documents/afile.txt"
        And response property "filename" should be "afile.txt"
        And response property "content" should be "Some content"
        And response property "raw" should be "/users/peej/repos/test/branches/master/raw/afile.txt"
        And response property "history" should be "/users/peej/repos/test/branches/master/history/afile.txt"

    Scenario: View a documents raw content
        Given I send a GET request on "/users/peej/repos/test/branches/master/raw/afile.txt"
        Then the response should contain "Some content"

    Scenario: View a documents history
        Given I send a GET request on "/users/peej/repos/test/branches/master/history/afile.txt"
        