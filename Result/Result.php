<?php

namespace PHRETS\Result;

use PHRETS\Response\Response; 
use PHRETS\Exception\NonUniqueResultException; 

class Result implements \IteratorAggregate, \Countable
{
    protected $response;
    protected $results;

    public function __construct(Response $response = null)
    {
        $this->response = $response;
    }

    /**
     * Get the response that generated this result
     * 
     * @return \PHRETS\Response\Response
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * Get all results in an array
     * 
     * @return array
     */
    public function getResults()
    {
        return $this->results;
    }

    /**
     * Add a result to the results array
     * 
     * @param mixed $result
     */
    public function addResult($result)
    {
        $this->results[] = $result;
    }

    /**
     * Force each to go through addResult so that it can perform
     * further actions on the result being added
     * 
     * @param type $results
     */
    public function setResults($results)
    {
        foreach($results as $result){
            $this->addResult($result);
        }
    }
    
    /**
     * Returns whether or not there are multiple results
     * 
     * @return boolean
     */
    public function hasMultiple()
    {
        return isset($this->results[1]); 
    }
    
    /**
     * If there is only a single result, return it
     * 
     * @return mixed
     * @throws NonUniqueResultException
     */
    public function getSingleResult()
    {
        if($this->hasMultiple()){
            throw new NonUniqueResultException('There is not a single result.'); 
        }
        
        return $this->results[0]; 
    }

    /**
     * Whether or not the result has an error
     * 
     * @return boolean
     */
    public function hasError()
    {
        return $this->response->hasError();
    }

    /**
     * Only XML Responses contain errors
     * 
     * @return string The error message
     */
    public function getError()
    {
        return $this->getResponse()->getError();
    }
    
    public function getIterator()
    {
        return new \ArrayIterator($this->getResults());
    }
    
    public function count()
    {
        return count($this->getResults());
    }
}