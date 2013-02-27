<?php

namespace PHRETS\Result; 

use PHRETS\Client\Response; 

abstract class AbstractResult
{
    protected $response; 
    
    public function __construct(Response $response)
    {
        $this->response = $response; 
    }
}