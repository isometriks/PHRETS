<?php

namespace PHRETS\Result; 

interface ResultInterface
{
    public function getResponse(); 
    
    public function getResults(); 
    public function addResult($result); 
    public function setResults($results); 
    
    public function hasMultiple(); 
    public function getSingleResult(); 
    
    public function hasError(); 
    public function getError(); 
}