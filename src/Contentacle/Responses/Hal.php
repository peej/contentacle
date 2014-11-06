<?php

namespace Contentacle\Responses;

class Hal extends \Tonic\Response
{
    private $links = array();
    private $embedded = array();

    public function __construct($code = null, $body = null, $headers = array())
    {
        parent::__construct($code, null, $headers);

        $this->contentType = 'application/hal+yaml';

        if (is_array($body)) {
            $this->body = $body;
        } elseif (is_object($body)) {
            $this->body = array();
            if (is_a($body, '\Contentacle\Models\Model')) {
                $this->body = $body->props();
            } else {
                foreach (get_object_vars($body) as $key => $value) {
                    $this->body[$key] = $value;
                }
            }
        } else {
            $this->body = array();
        }

        $this->addCuries();
    }

    private function hasContent()
    {
        if ($this->code == self::NOCONTENT) {
            $this->code = self::OK;
        }
    }

    public function addData($name, $value)
    {
        $this->body[$name] = $value;
        $this->hasContent();
    }

    private function addCuries() {
        $this->links['curies'][] = array(
            'name' => 'cont',
            'href' => 'http://contentacle.io/rels/{rel}',
            'templated' => true
        );
    }

    private function addLinkOrForm($rel, $href = null, $templated = false, $method = 'get', $contentType = null, $title = null)
    {
        $this->links[$rel] = array();
        if ($href) $this->links[$rel]['href'] = $href;
        if ($templated) $this->links[$rel]['templated'] = true;
        if ($method != 'get') $this->links[$rel]['method'] = $method;
        if ($contentType == '*/*') {
            $this->links[$rel]['content-type'] = $contentType;
        } elseif ($contentType) {
            $this->links[$rel]['content-type'] = array(
                $contentType.'+yaml',
                $contentType.'+json'
            );
        }
        if ($title) $this->links[$rel]['title'] = $title;
        $this->hasContent();
        $this->body['_links'] = $this->links;
    }

    public function addLink($rel, $href, $templated = false, $title = null)
    {
        $this->addLinkOrForm($rel, $href, $templated, 'get', null, $title);
    }

    public function addForm($rel, $method, $href = null, $contentType = null, $title = null, $templated = false)
    {
        if ($href == null) {
            $href = isset($this->links['self']['href']) ? $this->links['self']['href'] : null;
        }
        $this->addLinkOrForm($rel, $href, $templated, $method, $contentType, $title);
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

}
