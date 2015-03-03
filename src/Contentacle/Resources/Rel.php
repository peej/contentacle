<?php

namespace Contentacle\Resources;

/**
 * @uri /rels/{rel}
 */
class Doc extends Resource {

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

    private function getDocumentationFromDocComment($rel)
    {
        $className = 'Contentacle\Resources\\'.ucfirst($rel);

        if (!isset($this->app->resources[$className])) {
            throw new \Tonic\NotFoundException;
        }

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
        if ($rel == 'error') {
            return array(
                'get' => array(
                    'description' => 'Get a validation error.',
                    'request' => array(
                        'method' => array('get')
                    ),
                    'response' => array(
                        'code' => array('200 OK'),
                        'provides' => array('application/yaml', 'application/json')
                    )
                )
            );

        } else {
            return $this->getDocumentationFromDocComment($rel);
        }
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

        $response->addData('title', '/rels/'.$rel);
        $response->addData('actions', $this->getDocumentation($rel));

        return $response;
    }

}