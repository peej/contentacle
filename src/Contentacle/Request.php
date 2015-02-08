<?php

namespace Contentacle;

class Request extends \Tonic\Request
{
    public function addAccept($accept)
    {
        $this->accept[] = $accept;
    }
}