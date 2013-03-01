<?php

namespace PHRETS;

use PHRETS\Client\ClientInterface;
use PHRETS\Result\Result; 
use PHRETS\Result\SearchResult;
use PHRETS\Result\MetadataResult; 
use PHRETS\Result\Object;
use PHRETS\Response\XmlResponse; 


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

        $body         = $response->getBody(); 
        $delimiter    = isset($body->DELIMITER) ? (string)$body->DELIMITER->attributes()->value : 9; 
        
        $char         = chr($delimiter);
        $columns      = trim($body->COLUMNS[0], $char);
        $column_names = str_getcsv($columns, $char);

        $result->setColumnNames($column_names);

        $results = array();
        foreach ($body->DATA as $row) {
            $row       = trim($row, $char);
            $results[] = array_combine($column_names, str_getcsv($row, $char));
        }

        // Have to use setResults so the keys stay
        $result->setResults($results);

        return $result;
    }

    
    /**
     * Gets a media object
     * 
     * @param string $resource
     * @param string $type
     * @param array $id
     * @param int $location
     * @return \PHRETS\Result\Result
     */
    public function getObject($resource, $type, $id, $location = 0)
    {
        $properties = array(); 
        
        foreach($id as $property => $num){
            if(is_array($num)){
                $properties[] .= $property.':'.implode(':', $num); 
            } else {
                $properties[] .= $property.':'.$num; 
            }
        }
        
        $id_string = implode(',', $properties); 
        
        $params = array(
            'Resource' => $resource, 
            'Type' => $type, 
            'ID' => $id_string, 
            'Location' => $location, 
        ); 
        
        $response = $this->client->request('GetObject', $params, true); 
        $result   = new Result($response);
        $results  = $response->isMultipart() ? $response->getParts() : array($response); 
   
        foreach($results as $part){
            if($part instanceof XmlResponse){
                $result->addResult(new Result($part)); 
            } else {
                $result->addResult(new Object($part)); 
            }
        }
        
        return $result; 
    }
    
    /**
     * 
     * @param type $resource
     * @param type $lookup_name
     * @return \PHRETS\Result\Result A Result of LookupResults
     */
    public function getLookupValues($resource, $lookup_name = '*')
    {
        $params = array(
            'Type'   => 'METADATA-LOOKUP_TYPE', 
            'ID'     => $resource.':'.$lookup_name, 
            'Format' => 'STANDARD-XML', 
        );
        
        /**
         * @var \PHRETS\Response\XmlResponse Metadata Response
         */
        $response = $this->client->request('GetMetadata', $params); 
        $result = new Result($response); 
        $body = $response->getBody(); 
        
        if(isset($body->METADATA, $body->METADATA->{'METADATA-LOOKUP_TYPE'})){
            $lookups = $body->METADATA->{'METADATA-LOOKUP_TYPE'}; 
            
            foreach($lookups as $lookup){
                $properties = $lookup->attributes();
                
                $metadata_result = new MetadataResult(); 
                $metadata_result->setProperties($properties);
            
                if(isset($lookup->LookupType)){
                    $types = $lookup->LookupType; 
                } else {
                    $types = $lookup->Lookup; 
                }
                
                $metadata_result->setResults($types);                 
                $result->addResult($metadata_result); 
            }
        }      
        
        return $result; 
    }
    
    
    public function getMetadataTable($resource, $class = '*')
    {
        $params = array(
            'Type' => 'METADATA-TABLE', 
            'ID' => $resource.':'.$class, 
            'Format' => 'STANDARD-XML', 
        ); 
        
        $response = $this->client->request('GetMetadata', $params); 
        $result = new Result($response); 
        $body = $response->getBody(); 
        
        if(isset($body->METADATA, $body->METADATA->{'METADATA-TABLE'})){
            $fields = $body->METADATA->{'METADATA-TABLE'}; 
            
            foreach($fields as $field){
                $properties = $field->attributes();
                
                $metadata_result = new MetadataResult(); 
                $metadata_result->setProperties($properties);
                $metadata_result->setResults($field->Field); 
                                 
                $result->addResult($metadata_result); 
            }
        }      
        
        return $result; 
    }
}