<?php

namespace PHRETS\Response;

class Response
{
    protected $body;
    protected $http_code;
    protected $headers;
    protected $multipart; 
    protected $parts;

    /**
     * 
     * @param mixed $body Body of response
     * @param array $headers Response headers
     * @param int $http_code Response HTTP status code
     */
    public function __construct($body = null, array $headers = array(), $http_code = null)
    {
        $this->body      = $body;
        $this->http_code = $http_code;
        $this->headers   = $headers;
        $this->parts     = array(); 
        $this->multipart = false; 
    }
    
    /**
     * Get the body of the response
     * 
     * @return mixed
     */
    public function getBody()
    {
        return $this->body; 
    }
    
    /**
     * Set response body
     * 
     * @param mixed $body
     */
    public function setBody($body)
    {
        $this->body = $body; 
    }
    
    /**
     * Checks if reponse has multiple parts
     * 
     * @return boolean
     */
    public function isMultipart()
    {
        return isset($this->parts[0]); 
    }
    
    /**
     * Get parts
     * 
     * @return array
     */
    public function getParts()
    {
        return $this->parts; 
    }
    
    /**
     * Set parts
     * 
     * @param array $parts
     */
    public function setParts($parts)
    {
        $this->parts = $parts; 
    }
    
    /**
     * Add a part
     * 
     * @param \PHRETS\Response\Response $part
     */
    public function addPart(Response $part)
    {
        $this->parts[] = $part; 
    }

    /**
     * Get HTTP Status code
     * 
     * @return int
     */
    public function getHttpCode()
    {
        return $this->http_code;
    }  
    
    /**
     * Set HTTP Status code
     * 
     * @param type $http_code
     */
    public function setHttpCode($http_code)
    {
        $this->http_code = $http_code;
    }

    /**
     * Get all headers
     * 
     * @return array
     */
    public function getHeaders()
    {
        return $this->headers;
    }

    /**
     * Set headers
     * 
     * @param array $headers
     */
    public function setHeaders($headers)
    {
        $this->headers = $headers;
    }

    /**
     * Check if header exists
     * 
     * @param string $header
     * @return boolean
     */
    public function hasHeader($header)
    {
        return isset($this->headers[$header]);
    }

    /**
     * Gets header if it exists
     * 
     * @param string $header
     * @return string
     * @throws \InvalidArgumentException
     */
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