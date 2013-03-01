<?php

namespace PHRETS\Client;

use PHRETS\Response\Response;
use PHRETS\Response\XmlResponse; 

class CurlClient extends AbstractClient
{
    private $ch;
    private $cookie_file;

    public function connect($url, $username, $password, $ua_password = '')
    {
        parent::connect($url, $username, $password, $ua_password);

        /**
         * Prepare cURL
         */
        $this->ch = \curl_init();

        \curl_setopt_array($this->ch, array(
            CURLOPT_HEADER => false,
            CURLOPT_USERPWD => $this->username . ':' . $this->password,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_SSL_VERIFYPEER => false,
        ));


        /**
         * Cookie
         */
        if ($this->hasOption('cookie_file')) {
            $this->cookie_file = $this->getOption('cookie_file');
        } else {
            $this->cookie_file = tempnam('', 'phrets');
        }

        touch($this->cookie_file);

        \curl_setopt($this->ch, CURLOPT_COOKIEFILE, $this->cookie_file);

        /**
         * Set HTTP Auth
         */
        if ($this->getOption('force_basic_auth')) {
            \curl_setopt($this->ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        } else {
            \curl_setopt($this->ch, CURLOPT_HTTPAUTH, CURLAUTH_DIGEST | CURLAUTH_BASIC);
        }

        /**
         * Disable Follow Location
         */
        if (!$this->getOption('disable_follow_location')) {
            \curl_setopt($this->ch, CURLOPT_FOLLOWLOCATION, 1);
        }

        /**
         * Try to Login
         * 
         * @var \PHRETS\Response\XmlResponse Login Response
         */
        $response = $this->request('Login');

        if ($response->hasError()) {
            throw new \Exception($response->getError());
            
        } elseif (!$response instanceof XmlResponse) {
            throw new \Exception('Login did not return XML: ' . $response->getBody()); 
        }

        $this->connected = true; 
        $capabilities = $response->getRetsResponse();

        /**
         * Get the Capability URLs
         */
        foreach (explode("\n", trim($capabilities)) as $line) {
            list($name, $value) = explode("=", $line, 2);
            
            if (in_array($name, $this->allowed_capabilities)) {
                $this->setCapabilityUrl($name, $value);
            }
        }
        
        /**
         * Set some server details
         */
        if($response->hasHeader('RETS-Version')){
            $this->setServerDetail('version', $response->getHeader('RETS-Version')); 
        }

        /**
         * If Action capability URL is provided, we MUST request it following successful login
         */
        if ($this->hasCapabilityUrl('Action')) {
            $response = $this->request('Action');

            if (!$response) {
                throw new \Exception('Failed during "Action" request');
            }
        }

        return true;
    }

    public function disconnect()
    {
        if(!$this->connected){
            throw new \Exception('Cannot disconnect, not connected'); 
        }
        
        $response = $this->request('Logout');
        
        if($response->hasError()){
            throw new \Exception($response->getError()); 
        } elseif (!$response instanceof XmlResponse){
            throw new \Exception('Resopnse did not return XML: ' . $response->getBody()); 
        }
        
        $this->connected = false; 
        
        \curl_close($this->ch); 
        
        if(file_exists($this->cookie_file)){
            unlink($this->cookie_file); 
        }
    }
    
    /**
     * @param string $action A capability action. (Login, Logout, GetObject)
     * @param array $parameters Query parameters
     * @return \PHRETS\Response\ResponseInterface
     * @throws \InvalidArgumentException
     * @throws \Exception
     */
    public function request($action, array $parameters = array())
    {
        /**
         * Check for very first login before we have capability URLs
         */
        if ($action === 'Login' && !$this->hasCapabilityUrl('Login')) {
            $url = $this->url;
        } elseif ($this->hasCapabilityUrl($action)) {
            $url = $this->scheme . '://' . $this->host . ':' . $this->port . $this->getCapabilityUrl($action);
        } else {
            throw new \InvalidArgumentException(sprintf('There is no action for "%s"', $action));
        }


        /**
         * Prepare URL
         */
        if (count($parameters) > 0) {
            $url .= '?' . http_build_query($parameters);
        }

        \curl_setopt($this->ch, CURLOPT_URL, $url);

        /**
         * Check for User Agent Auth
         * 
         * I have no idea where $request_id is supposed to come from. It being
         * blank appears to work with no problem. 
         */
        if ($this->getOption('force_ua_auth')) {

            if (!$this->hasHeader('User-Agent')) {
                throw new \Exception('Forcing User Agent Auth without User Agent');
            }

            $this->setHeader('RETS-UA-Authorization', $this->getAuthDigest());
        }
        
        /**
         * Prepare Headers
         */
        $this->setHeader('RETS-Version', $this->getServerDetail('version'));
        
        $headers = '';
        foreach ($this->getHeaders() as $name => $value) {
            $headers .= $name . ': ' . $value . "\r\n";
        }
        
        \curl_setopt($this->ch, CURLOPT_HTTPHEADER, array($headers));

        /**
         * Get Headers
         */
        $headers = array();
        $_this = $this; 

        $callback = function($handle, $call_string) use (&$headers, $_this) {

                    if($pair = $_this->parseHeader($call_string)){
                        $headers[$pair[0]] = $pair[1]; 
                    }

                    return strlen($call_string);
                };

        \curl_setopt($this->ch, CURLOPT_HEADERFUNCTION, $callback);

        /**
         * Send Request
         */
        $body      = \curl_exec($this->ch);
        $http_code = \curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
        
        $response = $this->createResponse($body, $headers, $http_code); 
        $this->setLastResponse($response);

        /**
         * Check some headers 
         */
        if ($response->hasHeader('Set-Cookie')) {
            if (preg_match('/RETS-Session-ID\=(.*?)(\;|\s+|$)/', $response->getHeader('Set-Cookie'), $matches)) {
                $this->setSessionId($matches[1]);
            }
        }

        return $response;
    }
    
    /**
     * Checks a response for its content type. If it is a multipart, 
     * then we add sub-responses to the response. If it is XML
     * then we parse the xml for the body
     * 
     * @param type $body
     * @param type $headers
     * @param type $http_code
     * @return \PHRETS\Response\ResponseInterface
     */
    private function createResponse($body, $headers, $http_code = null)
    {
        $content_type = trim($headers['Content-Type']); 
        
        if(strpos($content_type, 'multipart') !== false){
            
            $response = new Response(null, $headers, $http_code); 
            $response->setMultipart(true); 
            
            preg_match('/boundary\=\"(.*?)\"/', $content_type, $matches);
            
            if(isset($matches[1])){
                $boundary = $matches[1]; 
            } else {
                preg_match('/boundary\=(.*?)(\s|$|\;)/', $content_type, $matches);
                $boundary = $matches[1]; 
            }
            
            // Strip quotes
            $boundary = '--' . trim($boundary, '"'); 
            
            // Clean body, remove preamble / epilogue
            $body = rtrim($body, '-');
            $body = trim($body, "\r\n"); 
            $body = trim($body, $boundary); 
            
            // Split 
            $parts = explode($boundary, $body); 
            
            /**
             * Add the sub-responses to the main response
             */
            foreach($parts as $part){
                list($raw_headers, $body) = explode("\r\n\r\n", trim($part), 2); 
                $headers = array(); 
                
                foreach(explode("\r\n", $raw_headers) as $string){
                    if($pair = $this->parseHeader($string)){
                        $headers[$pair[0]] = $pair[1]; 
                    }
                }
                
                $response->addPart($this->createResponse($body, $headers, $http_code)); 
            }
            
            return $response; 
        }
        
        if($content_type === 'text/xml'){
            $xml = @\simplexml_load_string($body); 
            
            $response = new XmlResponse($xml, $headers, $http_code); 
        } else {
            $response = new Response($body, $headers, $http_code); 
        }
        
        return $response; 
    }
}