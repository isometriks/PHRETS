<?php

namespace PHRETS\Client;

use PHRETS\Parser\XMLParser;

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
         * Some Defaults
         */
        if (!$this->hasHeader('RETS-Version')) {
            $this->setHeader('RETS-Version', 'RETS/1.5');
        }

        if (!$this->hasHeader('User-Agent')) {
            $this->setHeader('User-Agent', 'PHRETS/1.0');
        }

        if (!$this->hasHeader('Accept') && $this->getHeader('RETS-Version') === 'RETS/1.5') {
            $this->setHeader('Accept', '*/*');
        }


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
         */
        $response = $this->request('Login');

        if (!$response) {
            throw new \Exception('Could not log in');
        }

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

    /**
     * @param string $action A capability action. (Login, Logout, GetObject)
     * @param array $parameters Query parameters
     * @return \PHRETS\Client\Response
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
         * Prepare Headers
         */
        $headers = '';
        foreach ($this->getHeaders() as $name => $value) {
            $headers .= $name . ': ' . $value . "\r\n";
        }

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

            $ua_sum      = md5($this->getHeader('User-Agent') . ':' . $this->ua_password);
            $session_id  = $this->getOption('use_interealty_ua_auth') ? '' : $this->getSessionId();
            $ua_dig_resp = md5(trim($ua_sum) . ':' . trim($this->request_id) . ':' . $session_id . ':' . $this->getHeader('RETS-Version'));
            $headers .= 'RETS-UA-Authorization: Digest ' . $ua_dig_resp . "\r\n";
        }

        \curl_setopt($this->ch, CURLOPT_HTTPHEADER, array($headers));

        /**
         * Get Headers
         */
        $headers = array();

        $callback = function($handle, $call_string) use (&$headers) {

                    $trim = trim($call_string);

                    if (strpos($call_string, ':') !== false) {
                        list($header, $value) = explode(':', $call_string, 2);
                        $headers[rtrim($header)] = ltrim($value);
                    }

                    return strlen($call_string);
                };

        \curl_setopt($this->ch, CURLOPT_HEADERFUNCTION, $callback);

        /**
         * Send Request
         */
        $xml       = \curl_exec($this->ch);
        $http_code = \curl_getinfo($this->ch, CURLINFO_HTTP_CODE);
        $root      = @\simplexml_load_string($xml);

        if (!$root) {
            throw new \Exception('Invalid XML Response.');
        }

        $response = new Response($root, $http_code, $headers);
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
}