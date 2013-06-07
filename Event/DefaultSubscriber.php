<?php

namespace PHRETS\Event; 

use Symfony\Component\EventDispatcher\EventSubscriberInterface; 
use PHRETS\Response\XmlResponse; 

class DefaultSubscriber implements EventSubscriberInterface
{   
    /**
     * Before connecting, set some defaults
     * @param \PHRETS\Event\Event $event
     */
    public function onPreConnect(Event $event)
    {
        $client = $event->getClient(); 

        /**
         * Some Defaults
         */
        if ($client->hasHeader('RETS-Version')) {
            $client->setServerDetail('Version', $client->getHeader('RETS-Version')); 
        }

        if (!$client->hasHeader('User-Agent')) {
            $client->setHeader('User-Agent', 'PHRETS/1.0');
        }

        if (!$client->hasHeader('Accept') && $client->getServerDetail('Version') === 'RETS/1.5') {
            $client->setHeader('Accept', '*/*');
        }
        
        /**
         * Cookie
         */
        if (!$client->hasOption('cookie_file')) {
            $client->setOption('cookie_file', tempnam('', 'phrets'));
        }        
        
        touch($client->getOption('cookie_file'));
    }
    
    
    /**
     * Post Connect Listener
     * 
     * @param \PHRETS\Event\Event $event
     * @throws \Exception
     */
    public function onPostConnect(Event $event)
    {
        $client = $event->getClient(); 
        $response = $event->getResponse(); 
        
        if ($response->hasError()) {
            throw new \Exception($response->getError());
            
        } elseif (!$response instanceof XmlResponse) {
            throw new \Exception('Login did not return XML: ' . $response->getBody()); 
        }
        
        /**
         * Set the capabilities
         */
        $capabilities = $response->getRetsResponse();

        foreach (explode("\n", trim($capabilities)) as $line) {
            list($name, $value) = explode("=", $line, 2);
            
            if ($client->capabilityAllowed($name)) {
                $client->setCapabilityUrl($name, $value);
            } else {
                $client->setServerDetail($name, $value);
            }
        }        
                
        /**
         * Set some server details
         */
        if($response->hasHeader('RETS-Version')){
            $client->setServerDetail('Version', $response->getHeader('RETS-Version')); 
        }

        /**
         * If Action capability URL is provided, we MUST request it following successful login
         */
        if ($client->hasCapabilityUrl('Action')) {
            $response = $client->request('Action');

            if (!$response) {
                throw new \Exception('Failed during "Action" request');
            }
        }
    }
    
    
    /**
     * Get the subscribed events
     * 
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            Events::PRE_CONNECT => array('onPreConnect', 64),
            Events::POST_CONNECT => array('onPostConnect', 64), 
        ); 
    }
}