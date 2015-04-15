<?php

namespace Contentacle\Resources;

/**
 * @uri /rels/{rel}
 */
class Rel extends Resource {

    private $resourceMap = array(
        'branch' => 'Branch',
        'branches' => 'Branches',
        'commit' => 'Commit',
        'commits' => 'Commits',
        'doc' => 'Rel',
        'document' => 'Document',
        'documents' => 'Document',
        'history' => 'History',
        'merge' => 'Merge',
        'merges' => 'Merges',
        'repo' => 'Repo',
        'repos' => 'Repos',
        'profile' => 'User',
        'raw' => 'Raw',
        'user' => 'User',
        'users' => 'Users'
    );

    function __construct($deps)
    {
        parent::__construct($deps);

        $this->resourceMap['error'] = array(
            'title' => 'Error',
            'description' => 'An error',
            'actions' => array(
                'get' => array(
                    'description' => 'Get a validation error.',
                    'response' => array(
                        'field' => array(
                            'logref' => 'Field name',
                            'message' => 'Error message'
                        )
                    )
                )
            )
        );
    }

    private function parseDocComment($docComment)
    {
        $data = array();
        $description = '';

        preg_match('/^[^@]*@/s', $docComment, $match);
        if (isset($match[0])) {
            foreach (explode("\n", $match[0]) as $line) {
                $description .= trim($line, "/* @\t");
            }
        }
        $data[] = array('description', $description);

        preg_match_all('/^\s*\*\s*@(.+)$/m', $docComment, $matches);
        if (isset($matches[1]) && $matches[1]) {
            foreach ($matches[1] as $match) {
                $data[] = explode(' ', $match);
            }
        }

        return $data;
    }

    private function getResourceDocumentation($className)
    {
        $data = array();
        $classReflector = new \ReflectionClass($className);
        $docComment = $classReflector->getDocComment();
        $classData = $this->parseDocComment($docComment);

        foreach ($classData as $item) {
            $key = array_shift($item);
            $data[$key] = join(' ', $item);
        }

        return $data;
    }

    private function getMethodDocumentation($className)
    {
        $data = array();

        foreach (get_class_methods($className) as $methodName) {
            $methodReflector = new \ReflectionMethod($className, $methodName);
            $docComment = $methodReflector->getDocComment();
            $parsedComment = $this->parseDocComment($docComment);

            $method = array();
            $section = 'request';
            foreach ($parsedComment as $item) {
                $key = array_shift($item);

                if ($key == 'field' || $key == 'header' || $key == 'links' || $key == 'embeds') {
                    $key2 = array_shift($item);
                    $value = array($key2 => join(' ', $item));

                } else {
                    $value = join(' ', $item);
                }

                if ($key == 'description') {
                    $method['description'] = $value;

                } else {
                    if ($key == 'response') {
                        $key = 'code';
                        $section = 'response';
                    }

                    if (!isset($method[$section][$key])) {
                        $method[$section][$key] = array();
                    }
                    if (is_array($value)) {
                        $method[$section][$key] = array_merge($method[$section][$key], $value);
                    } else {
                        $method[$section][$key][] = $value;
                    }
                }
            }
            if (isset($method['request']['method'])) {
                $data[$methodName] = $method;
            }
        }

        return $data;
    }

    private function getDocumentation($rel)
    {
        if (isset($this->resourceMap[$rel])) {

            if (is_array($this->resourceMap[$rel])) {
                return $this->resourceMap[$rel];
            }

            $className = 'Contentacle\\Resources\\'.$this->resourceMap[$rel];

            if (isset($this->app->resources[$className])) {
                $data = $this->getResourceDocumentation($className);

                if (!isset($data['title'])) {
                    $data['title'] = ucwords($rel);
                }

                $data['actions'] = $this->getMethodDocumentation($className);

                return $data;
            }
        }

        throw new \Tonic\NotFoundException;
    }

    /**
     * Get documentation for the link relation.
     *
     * @method get
     * @response 200 OK
     * @provides application/hal+yaml
     * @provides application/hal+json
     * @provides text/html
     */
    function get($rel)
    {
        $response = $this->response(200, 'rel');

        $data = $this->getDocumentation($rel);

        $response->addData('title', $data['title']);
        $response->addData('actions', $data['actions']);

        if (isset($data['description']) && $data['description']) {
            $response->addData('description', $data['description']);
        }

        return $response;
    }

}