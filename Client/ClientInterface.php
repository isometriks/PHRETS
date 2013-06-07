<?php

namespace PHRETS\Client; 

use Symfony\Component\EventDispatcher\EventSubscriberInterface; 

interface ClientInterface
{    
    public function connect(); 
    public function disconnect(); 
    
    /**
     * @return \PHRETS\Response\Response Response
     */
    public function request($action, array $parameters = array());
    
    /**
     * Gets the Reguest URL for a certain action
     * 
     * @param string $action
     * @param array $parameters
     */
    public function getRequestUrl($action, array $parameters = array()); 
    
    public function hasHeader($name); 
    public function getHeader($name); 
    public function getHeaders(); 
    public function setHeader($name, $value); 
    public function removeHeader($name); 
    
    public function parseHeader($string); 
    
    public function hasOption($name); 
    public function setOption($name, $value); 
    public function getOption($name); 
    
    public function setServerDetail($detail, $value); 
    public function getServerDetail($detail);
    public function getServerDetails(); 
    
    public function getSessionId(); 
    public function setSessionId($session_id); 
    
    /**
     * If available, returns the last response
     * 
     * @return \PHRETS\Response\Response $response
     */
    public function getLastResponse(); 
    public function setLastResponse($response);
    
    public function capabilityAllowed($capability); 
    public function hasCapabilityUrl($capability); 
    public function getCapabilityUrl($capability); 
    public function setCapabilityUrl($capability, $url); 
    
    
    public function getEventSubscribers(); 
    public function addEventSubscriber(EventSubscriberInterface $subscriber); 
}