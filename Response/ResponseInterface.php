<?php

namespace PHRETS\Response; 

interface ResponseInterface
{
    public function getBody(); 
    public function setBody($body); 
    
    public function getHeaders(); 
    public function setHeaders($headers); 
    public function hasHeader($header); 
    public function getHeader($header); 
    
    public function getHttpCode(); 
    public function setHttpCode($http_code); 
    
    public function setMultipart($multipart); 
    public function isMultipart(); 
    
    public function getParts(); 
    public function addPart($part); 
    public function setParts($parts); 
    
    public function hasError(); 
}