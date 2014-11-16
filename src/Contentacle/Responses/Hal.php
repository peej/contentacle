<?php

namespace Contentacle\Responses;

class Hal extends \Tonic\Response
{
    private $yaml;
    public $body = array();
    private $links = array();
    private $embedded = array();

    public function __construct($yaml, $code = null, $body = null, $headers = array())
    {
        parent::__construct($code, null, $headers);

        $this->yaml = $yaml;
        $this->contentType = 'application/hal+yaml';
        $this->addData($body);
        $this->addCuries();
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

    public function addData($name, $value = null)
    {
        if (is_array($name)) {
            foreach ($name as $key => $value) {
                $this->body[$key] = $value;
            }
        } elseif (is_object($name)) {
            if (is_a($name, '\Contentacle\Models\Model')) {
                foreach ($name->props() as $key => $value) {
                    $this->body[$key] = $value;
                }
            } else {
                foreach (get_object_vars($name) as $key => $value) {
                    $this->body[$key] = $value;
                }
            }
        } elseif (is_string($name)) {
            $this->body[$name] = $value;
        }

        if ($this->body) {
            $this->hasContent();
        }
    }

    private function addCuries() {
        $this->links['curies'][] = array(
            'name' => 'cont',
            'href' => 'http://contentacle.io/rels/{rel}',
            'templated' => true
        );
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
        $this->body['_links'] = $this->links;
    }

    public function embed($rel, $document)
    {
        if (isset($document['_links']['curies'])) {
            unset($document['_links']['curies']);
        }
        $this->embedded[$rel][] = $document;
        $this->hasContent();
        $this->body['_embedded'] = $this->embedded;
    }

    public function output()
    {
        foreach ($this->headers as $name => $value) {
            header($name.': '.$value, true, $this->responseCode());
        }

        if (is_array($this->body)) {
            if (substr($this->contentType, -4) == 'json') {
                echo json_encode($this->body, JSON_PRETTY_PRINT);
            } else {
                echo $this->yaml->encode($this->body);
            }
        }
    }

}
