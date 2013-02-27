<?php

namespace PHRETS; 

use PHRETS\Client\ClientInterface; 
use PHRETS\Result\SearchResult; 

class phRETS
{
    private $client; 
    
    public function __construct(ClientInterface $client)
    {
        $this->client = $client; 
    }
    
    /**
     * 
     * @param type $resource
     * @param type $class
     * @param type $query
     * @param array $params
     * @return \PHRETS\Result\SearchResult
     */
    public function search($resource, $class, $query = '', array $params = array())
    {
        $defaults = array(
            'Format' => 'COMPACT-DECODED', 
            'SearchType' => $resource, 
            'Class' => $class, 
            'Count' => 1, 
            'Limit' => 99999999, 
            'Query' => $query, 
            'QueryType' => 'DMQL2', 
            'StandardNames' => $this->client->getOption('use_standard_names') ? 1 : 0, 
        ); 
        
        $params   = array_replace($defaults, $params);
        $response = $this->client->request('Search', $params);
        
        $result = new SearchResult($response); 
        
        if($response->getDelimiter()){
            $char = chr($response->getDelimiter()); 
            $columns = trim($response->getColumns(), $char); 
            $column_names = explode($char, $columns); 
            
            $result->setColumnNames($column_names); 
            
            $results = array(); 
            foreach($response->getData() as $row){
                $row = trim($row, $char); 
                $results[] = array_combine($column_names, str_getcsv($row, $char)); 
            }
            
            $result->setResults($results); 
        }
     
        return $result; 
    }
    
    public function getObject($resource, $type, $id, $number='*', $location=0)
    {
        $number = str_replace(',', ':', $number); 
        $number = str_replace(' ', '', $number); 
    }
    
    
    
}