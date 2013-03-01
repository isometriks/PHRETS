<?php

namespace PHRETS\Response;

class Response
{
    protected $body;
    protected $http_code;
    protected $headers;
    protected $multipart; 
    protected $parts;

    public function __construct($body = null, $headers = null, $http_code = null)
    {
        $this->body      = $body;
        $this->http_code = $http_code;
        $this->headers   = $headers;
        $this->parts     = array(); 
        $this->multipart = false; 
    }
    
    public function getBody()
    {
        return $this->body; 
    }
    
    public function setBody($body)
    {
        $this->body = $body; 
    }
    
    public function setMultipart($multipart)
    {
        $this->multipart = $multipart; 
    }
    
    public function isMultipart()
    {
        return $this->multipart; 
    }
    
    public function getParts()
    {
        return $this->parts; 
    }
    
    public function setParts($parts)
    {
        $this->parts = $parts; 
    }
    
    public function addPart($part)
    {
        $this->parts[] = $part; 
    }

    public function getHttpCode()
    {
        return $this->http_code;
    }  
    
    public function setHttpCode($http_code)
    {
        $this->http_code = $http_code;
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
    
    public function hasError()
    {
        return false;
    }
    
    public function getError()
    {
        return ''; 
    }
}