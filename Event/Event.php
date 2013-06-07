<?php

namespace PHRETS\Event; 

use PHRETS\Client\ClientInterface; 
use PHRETS\Response\Response; 
use Symfony\Component\EventDispatcher\Event as BaseEvent; 


class Event extends BaseEvent
{
    private $client; 
    private $response; 
    
    public function __construct(ClientInterface $client, Response $response = null)
    {
        $this->client = $client;
        $this->response = $response; 
    }
    
    /**
     * @return ClientInterface Client
     */
    public function getClient()
    {
        return $this->client; 
    }
    
    /**
     * @param \PHRETS\Response\Response $response
     */
    public function setResponse(Response $response)
    {
        $this->response = $response; 
    }
    
    /**
     * @return Response
     */
    public function getResponse()
    {
        return $this->response; 
    }
}