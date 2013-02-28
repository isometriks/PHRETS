<?php

namespace PHRETS\Result;

use PHRETS\Response\ResponseInterface; 
use PHRETS\Exception\NonUniqueResultException; 

abstract class AbstractResult implements ResultInterface, \IteratorAggregate, \Countable
{
    protected $response;
    protected $results;

    public function __construct(ResponseInterface $response = null)
    {
        $this->response = $response;
    }

    /**
     * @return \PHRETS\Response\ResponseInterface
     */
    public function getResponse()
    {
        return $this->response;
    }

    public function getResults()
    {
        return $this->results;
    }

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
    
    public function hasMultiple()
    {
        return isset($this->results[1]); 
    }
    
    public function getSingleResult()
    {
        if($this->hasMultiple()){
            throw new NonUniqueResultException('There is not a single result.'); 
        }
        
        return $this->results[0]; 
    }

    public function hasError()
    {
        return $this->response->hasError();
    }

    /**
     * Only XML Responses contain errors
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