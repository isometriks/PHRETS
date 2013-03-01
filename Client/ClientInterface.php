<?php

namespace PHRETS\Client; 

use PHRETS\Client\Response; 

interface ClientInterface
{    
    public function connect($url, $username, $password, $ua_password = ''); 
    public function disconnect(); 
    
    /**
     * @return \PHRETS\Response\ResponseInterface Response
     */
    public function request($action, array $parameters = array());
    
    public function hasHeader($name); 
    public function getHeader($name); 
    public function getHeaders(); 
    public function setHeader($name, $value); 
    public function removeHeader($name); 
    
    public function parseHeader($string); 
    
    public function hasOption($name); 
    public function setOption($name, $value); 
    public function getOption($name); 
    
    public function getServerDetail($detail);
    public function getServerDetails(); 
    
    public function getSessionId(); 
    public function setSessionId($session_id); 
    
    /**
     * If available, returns the last response
     * 
     * @return \PHRETS\Response\ResponseInterface $response
     */
    public function getLastResponse(); 
    public function setLastResponse($response);
    
    public function hasCapabilityUrl($capability); 
    public function getCapabilityUrl($capability); 
    public function setCapabilityUrl($capability, $url); 
}