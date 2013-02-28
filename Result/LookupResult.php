<?php

namespace PHRETS\Result;

class LookupResult extends AbstractResult
{
    protected $lookup; 
    protected $resource; 
    protected $date; 
    protected $version; 
    
    /**
     * Force the SimpleXMLElement to an array
     * 
     * TODO: Do we want to change the array keys to be one of the lookup
     * values so you can find it easily?
     * 
     * @param type $result
     */
    public function addResult($result)
    {
        parent::addResult((array)$result);
    }
    
    public function getLookup()
    {
        return $this->lookup; 
    }
    
    public function setLookup($lookup)
    {
        $this->lookup = $lookup; 
    }
    
    public function getResource()
    {
        return $this->resource; 
    }
    
    public function setResource($resource)
    {
        $this->resource = $resource; 
    }
    
    public function getDate()
    {
        return $this->date; 
    }
    
    public function setDate($date)
    {
        $this->date = $date; 
    }
    
    public function getVersion()
    {
        return $this->version; 
    }
    
    public function setVersion($version)
    {
        $this->version = $version; 
    }

}