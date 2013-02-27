<?php

namespace PHRETS\Client;

class Response
{
    private $root;
    private $http_code;
    private $headers;

    public function __construct($root = null, $http_code = null, $headers = null)
    {
        $this->root      = $root;
        $this->http_code = $http_code;
        $this->headers   = $headers;
    }

    public function getRetsResponse()
    {
        return (string) $this->root->{'RETS-RESPONSE'};
    }

    public function getColumns()
    {
        return $this->root->COLUMNS[0];
    }

    public function getDelimiter()
    {
        if (isset($this->root->DELIMITER)) {
            return (string) $this->root->DELIMITER->attributes()->value;
        }

        return false;
    }

    public function getData()
    {
        return $this->root->DATA;
    }

    public function getRoot()
    {
        return $this->root;
    }

    public function setRoot($root)
    {
        $this->root = $root;
    }

    public function getReplyText()
    {
        return (string) $this->root['ReplyText'];
    }

    public function getReplyCode()
    {
        return (int) $this->root['ReplyCode'];
    }

    public function getHttpCode()
    {
        return $this->http_code;
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function setHeaders($headers)
    {
        $this->headers = $headers;
    }

    public function hasHeader($header)
    {
        return isset($this->headers[$header]);
    }

    public function getHeader($header)
    {
        if (!$this->hasHeader($header)) {
            throw new \InvalidArgumentException(sprintf('Header "%s" does not exist', $header));
        }

        return $this->headers[$header];
    }
}