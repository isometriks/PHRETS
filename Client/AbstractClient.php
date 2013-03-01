<?php

namespace PHRETS\Client;

abstract class AbstractClient implements ClientInterface
{
    protected $headers = array();
    protected $options;
    protected $last_response;
    protected $url;
    protected $ua_password;
    protected $host;
    protected $port;
    protected $scheme;
    protected $path;
    protected $query;
    protected $session_id;
    protected $request_id = '';
    protected $capability_urls;
    protected $connected; 
    
    protected $server_details = array(
        'version' => 'RETS/1.5', 
    ); 
    
    protected $allowed_options = array(
        'cookie_file', 'debug_file', 'debug_mode', 'compression_enabled',
        'force_ua_auth', 'disable_follow_location', 'force_basic_auth',
        'use_interealty_ua_auth', 'catch_last_response', 'disable_encoding_fix',
        'offset_support', 'override_offset_protection', 'use_standard_names',
    );
    
    protected $allowed_capabilities = array(
        'Action', 'ChangePassword', 'GetObject', 'Login', 'LoginComplete',
        'Logout', 'Search', 'GetMetadata', 'ServerInformation', 'Update',
    );

    public function __construct(array $options = array())
    {
        $this->connected = false; 
        
        foreach ($options as $name => $value) {
            $this->setOption($name, $value);
        }
    }

    public function connect($url, $username, $password, $ua_password = null)
    {
        $parts        = parse_url($url);
        $this->host   = $parts['host'];
        $this->port   = isset($parts['port']) ? $parts['port'] : 80;
        $this->path   = $parts['path'];
        $this->scheme = $parts['scheme'];
        $this->query  = isset($parts['query']) ? $parts['query'] : '';

        $this->url         = $url;
        $this->username    = $username;
        $this->password    = $password;
        $this->ua_password = $ua_password;

        if ($ua_password !== null) {
            $this->setOption('force_ua_auth', true);
        }
        
        /**
         * Some Defaults
         */
        if ($this->hasHeader('RETS-Version')) {
            $this->setServerDetail('version', $this->getHeader('RETS-Version')); 
        }

        if (!$this->hasHeader('User-Agent')) {
            $this->setHeader('User-Agent', 'PHRETS/1.0');
        }

        if (!$this->hasHeader('Accept') && $this->getServerDetail('version') === 'RETS/1.5') {
            $this->setHeader('Accept', '*/*');
        }        
    }

    public function getLastResponse()
    {
        if (!$this->last_response === null) {
            throw new \Exception('No last Reponse available.');
        }

        return $this->last_response;
    }

    public function setLastResponse($response)
    {
        $this->last_response = $response;
    }

    public function hasOption($name)
    {
        return isset($this->options[$name]);
    }

    public function setOption($name, $value)
    {
        if (!in_array($name, $this->allowed_options)) {
            throw new \InvalidArgumentException(sprintf('Option "%s" does not exist', $name));
        }

        $this->options[$name] = $value;
    }

    /**
     * We don't need to throw an exception here I don't think.. Just 
     * return false if there is no option. 
     */
    public function getOption($name)
    {
        if (!$this->hasOption($name)) {
            return false;
        }

        return $this->options[$name];
    }

    public function hasHeader($name)
    {
        return isset($this->headers[$name]);
    }

    public function setHeader($name, $value)
    {
        $this->headers[$name] = $value;
    }

    public function removeHeader($name)
    {
        if (!$this->hasHeader($name)) {
            throw new \InvalidArgumentException(sprintf('Header "%s" does not exist', $name));
        }
    }

    public function getHeader($name)
    {
        if (!isset($this->headers[$name])) {
            throw new \InvalidArgumentException(sprintf('Header "%s" does not exist', $name));
        }

        return $this->headers[$name];
    }

    public function getHeaders()
    {
        return $this->headers;
    }

    public function hasCapabilityUrl($capability)
    {
        return isset($this->capability_urls[$capability]);
    }

    public function getCapabilityUrl($capability)
    {
        if (!$this->hasCapabilityUrl($capability)) {
            throw new \InvalidArgumentException(sprintf('URL for capability "%s" does not exist', $capability));
        }

        return $this->capability_urls[$capability];
    }

    public function setCapabilityUrl($capability, $url)
    {
        if (!in_array($capability, $this->allowed_capabilities)) {
            throw new \InvalidArgumentException(sprintf('Capability "%s" not allowed', $capability));
        }

        $this->capability_urls[$capability] = $url;
    }

    public function getSessionId()
    {
        return $this->session_id;
    }

    public function setSessionId($session_id)
    {
        $this->session_id = $session_id;
    }
    
    public function parseHeader($string)
    {
        if (strpos($string, ':') !== false) {
            list($header, $value) = explode(':', $string, 2);
        
            return array(
                trim($header), 
                trim($value),
            ); 
        }
        
        return false; 
    }
    
    protected function getAuthDigest()
    {
        $ua_sum      = md5($this->getHeader('User-Agent') . ':' . $this->ua_password);
        $session_id  = $this->getOption('use_interealty_ua_auth') ? '' : $this->getSessionId();
        $ua_dig_resp = md5(trim($ua_sum) . ':' . trim($this->request_id) . ':' . $session_id . ':' . $this->getServerDetail('version'));
            
        return 'Digest ' . $ua_dig_resp; 
    }
    
    protected function setServerDetail($detail, $value)
    {
        $this->server_details[$detail] = $value; 
    }
    
    public function getServerDetail($detail)
    {
        return isset($this->server_details[$detail]) ? $this->server_details[$detail] : null;
    }
    
    public function getServerDetails()
    {
        return $this->server_details; 
    }
}