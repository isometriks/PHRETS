<?php

namespace PHRETS\Response; 

class XmlResponse extends Response
{
    public function hasError()
    {
        return $this->getReplyCode() !== 0; 
    }
    
    public function getError()
    {
        return sprintf('(%d) %s', $response->getReplyCode(), $response->getReplyText());
    }
    
    public function getReplyCode()
    {
        $body = $this->getBody(); 
        return (int)$body['ReplyCode'];
    }
    
    public function getReplyText()
    {
        $body = $this->getBody(); 
        return (string)$body['ReplyText'];
    }
    
    public function getRetsResponse()
    {
        $body = $this->getBody(); 
        return (string)$body->{'RETS-RESPONSE'};
    }    
}