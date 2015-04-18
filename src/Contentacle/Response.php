<?php

namespace Contentacle;

class Response extends \Tonic\Response
{
    private $yaml;
    private $engine;
    private $templateName;
    private $vars = array(
        'footer' => true
    );
    public $data = array();
    private $links = array();
    private $embedded = array();

    public function __construct($code, $templateName, $yaml, $templateEngine)
    {
        parent::__construct($code);

        $this->contentType = 'application/hal+yaml';
        $this->templateName = $templateName;
        $this->yaml = $yaml;
        $this->engine = $templateEngine;
    }

    public function getCode()
    {
        return $this->code;
    }

    private function hasContent()
    {
        if ($this->code == self::NOCONTENT) {
            $this->code = self::OK;
        }
    }

    public function addVar($name, $value)
    {
        $this->vars[$name] = $value;
    }

    public function addData($name, $value = null)
    {
        if (is_array($name)) {
            foreach ($name as $key => $value) {
                $this->data[$key] = $value;
            }
        } elseif (is_object($name)) {
            if (is_a($name, '\Contentacle\Models\Model')) {
                foreach ($name->props() as $key => $value) {
                    $this->data[$key] = $value;
                }
            } else {
                foreach (get_object_vars($name) as $key => $value) {
                    $this->data[$key] = $value;
                }
            }
        } elseif (is_string($name)) {
            $this->data[$name] = $value;
        }

        if ($this->data) {
            $this->hasContent();
        }
    }

    public function addLink($rel, $href, $templated = false, $title = null)
    {
        $link = array();
        if ($href) $link['href'] = $href;
        if ($templated) $link['templated'] = true;
        if ($title) $link['title'] = $title;

        if (isset($this->links[$rel])) {
            if (isset($this->links[$rel][0])) {
                $this->links[$rel][] = $link;
            } else {
                $this->links[$rel] = array(
                    $this->links[$rel],
                    $link
                );
            }
        } else {
            $this->links[$rel] = $link;
        }

        $this->hasContent();
        $this->data['_links'] = $this->links;
    }

    public function embed($rel, $document)
    {
        if (isset($document['_links']['curies'])) {
            unset($document['_links']['curies']);
        }
        $this->embedded[$rel][] = $document;
        $this->hasContent();
        $this->data['_embedded'] = $this->embedded;
    }

    public function addError($field, $message = null)
    {
        $this->addErrors(array($field => $message));
    }

    public function addErrors($errors)
    {
        foreach ($errors as $field => $message) {
            $this->vars['error'][$field] = true;

            if (!$message) {
                $message = '"'.$field.'" field failed validation';
            }

            $this->embed('cont:error', array(
                'logref' => $field,
                'message' => $message
            ));
        }
    }

    private function addCuries() {
        if (count($this->links)) {
            $this->links['curies'][] = array(
                'name' => 'cont',
                'href' => '/rels/{rel}',
                'templated' => true
            );
            $this->data['_links'] = $this->links;
        }
    }

    private function calculateContentType($accept)
    {
        $contentTypes = array(
            'application/hal+yaml',
            'application/hal+json',
            'text/html'
        );

        if (count($accept) == 0) {
            return $contentTypes[0];
        }

        $matchingContentTypes = array_intersect($contentTypes, $accept);

        if (!$matchingContentTypes) {
            return $contentTypes[0];
        } else {
            return reset($matchingContentTypes);
        }
    }

    private function sendFriendlyMimetypeIfBrowser()
    {
        if (
            isset($_SERVER['HTTP_USER_AGENT']) &&
            strpos($_SERVER['HTTP_USER_AGENT'], 'Mozilla') !== false
        ) {
            header('Content-Type: text/plain', true);
        }
    }

    public function render($request)
    {
        $this->contentType = $this->calculateContentType($request->accept);

        foreach ($this->headers as $name => $value) {
            header($name.': '.$value, true, $this->responseCode());
        }

        switch ($this->contentType) {
        case 'text/html':
            if ($this->code == 201 && $this->location) {
                header('Location: '.$this->location, true, 303);
                exit;
            } else {
                $this->data['_get'] = $_GET;
                $this->data['_post'] = $_POST;
                $this->data['var'] = $this->vars;

                echo $this->engine->render($this->templateName, $this->data);
            }
            break;

        case 'application/hal+json':
        case 'application/json':
        case 'text/json':
            $this->sendFriendlyMimetypeIfBrowser();
            $this->addCuries();
            ksort($this->data);
            echo json_encode($this->data, JSON_PRETTY_PRINT);
            break;

        case 'application/hal+yaml':
        case 'application/yaml':
        case 'text/yaml':
        default:
            $this->sendFriendlyMimetypeIfBrowser();
            $this->addCuries();
            ksort($this->data);

            if ($this->yaml) {
                $yaml = new $this->yaml;
                echo $yaml->encode($this->data);
            } else {
                var_export($this->data);
            }
            break;
        }
    }

}