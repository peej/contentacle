<?php

namespace Contentacle\Responses;

class Hal extends \Tonic\Response
{
    private $links = array();
    private $embedded = array();

    public function __construct($code = null, $body = null, $headers = array())
    {
        parent::__construct($code, null, $headers);

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

        #$this->addCuries();
    }

    private function hasContent()
    {
        if ($this->code == self::NOCONTENT) {
            $this->code = self::OK;
        }
    }

    public function addData($name, $value)
    {
        $this->data[$name] = $value;
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
        if ($templated) $this->links[$rel]['templated'] = 'true';
        if ($method != 'get') $this->links[$rel]['method'] = $method;
        if ($contentType) {
            if (!is_array($contentType)) {
                $contentType = array($contentType);
            }
            $this->links[$rel]['content-type'] = $contentType;
        }
        if ($title) $this->links[$rel]['title'] = $title;
        $this->hasContent();
        $this->body['_links'] = $this->links;
    }

    public function addLink($rel, $href, $templated = false, $title = null)
    {
        $this->addLinkOrForm($rel, $href, $templated, 'get', null, $title);
    }

    public function addForm($rel, $method, $href = null, $title = null, $contentType = null)
    {
        if ($href == null) {
            $href = isset($this->links['self']['href']) ? $this->links['self']['href'] : null;
        }
        
        if ($contentType == null) {
            switch ($method) {
            case 'post':
            case 'put':
                $contentType = array('application/hal+yaml', 'application/hal+json');
                break;
            case 'patch':
                $contentType = array('application/json-patch+yaml', 'application/json-patch+json');
                break;
            }
        }

        $this->addLinkOrForm($rel, $href, false, $method, $contentType, $title);
    }

    public function embed($rel, $document)
    {
        $this->embedded[$rel][] = $document;
        $this->hasContent();
        $this->body['_embedded'] = $this->embedded;
    }

}
