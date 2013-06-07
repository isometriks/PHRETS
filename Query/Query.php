<?php

namespace PHRETS\Query; 

use PHRETS\phRETS; 

class Query
{
    private $conditions; 
    
    public function __construct(array $conditions = array())
    {
        $this->conditions = $conditions; 
    }
    
    public function greaterThan(array $conditions = array())
    {
        foreach($conditions as $field => $value){
            $this->conditions[$field] = rtrim($value, '+').'+'; 
        }
    }
}