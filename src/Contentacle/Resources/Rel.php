<?php

namespace Contentacle\Resources;

/**
 * @uri /rels/{rel}
 */
class Rel extends Resource {

    private function parseDocComment($docComment)
    {
        $data = array(
            'description' => ''
        );

        preg_match('/^[^@]*@/s', $docComment, $match);
        if (isset($match[0])) {
            foreach (explode("\n", $match[0]) as $line) {
                $data['description'] .= trim($line, "/* @\t");
            }
        }

        preg_match_all('/^\s*\*\s*@(.+)$/m', $docComment, $matches);
        if (isset($matches[1]) && $matches[1]) {
            foreach ($matches[1] as $match) {
                $parts = explode(' ', $match);
                $key = array_shift($parts);

                switch ($key) {
                case 'field':
                case 'header':
                case 'links':
                case 'embeds':
                    if (!isset($data[$key])) {
                        $data[$key] = array();
                    }
                    $key2 = array_shift($parts);
                    $data[$key][$key2] = join(' ', $parts);
                    break;
                default:
                    if (isset($data[$key])) {
                        $data[$key][] = join(' ', $parts);
                    } else {
                        $data[$key] = array(join(' ', $parts));
                    }
                }
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

            if (isset($parsedComment['method'])) {
                foreach ($parsedComment['method'] as $method) {
                    $data[$method] = $parsedComment;
                }
            }
        }

        return $data;
    }

    /**
     * @method get
     * @provides application/hal+yaml
     * @provides application/hal+json
     */
    function get($rel)
    {
        return $this->createHalResponse(200, $this->getDocumentationFromDocComment($rel));
    }

    /**
     * @method get
     * @provides text/html
     */
    function getHtml($rel)
    {
        $data = array(
            'title' => '/rels/'.$rel,
            'methods' => $this->getDocumentationFromDocComment($rel)
        );
        return $this->createHtmlResponse('rel.html', $data);
    }

}