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

        $result       = new SearchResult($response);
        $body         = $response->getBody();
        $delimiter    = isset($body->DELIMITER) ? (string) $body->DELIMITER->attributes()->value : 9;

        $char         = chr($delimiter);
        $columns      = trim($body->COLUMNS[0], $char);
        $column_names = str_getcsv($columns, $char);

        $result->setColumnNames($column_names);

        foreach ($body->DATA as $row) {
            $row       = trim($row, $char);
            $result->addResult(array_combine($column_names, str_getcsv($row, $char)));
        }

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

        foreach ($id as $property => $num) {
            if (is_array($num)) {
                $properties[] .= $property . ':' . implode(':', $num);
            } else {
                $properties[] .= $property . ':' . $num;
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

        foreach ($results as $part) {
            if ($part instanceof XmlResponse) {
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
            'ID' => $resource . ':' . $lookup_name,
        );

        return $this->getMetadata($params, 'METADATA-LOOKUP_TYPE', array('LookupType', 'Lookup'));
    }

    /**
     * @param type $resource Resource Name
     * @param mixed $class int, string, or *
     * @return type
     */
    public function getMetadataTable($resource, $class = '*')
    {
        $params = array(
            'ID' => $resource . ':' . $class,
        );

        return $this->getMetadata($params, 'METADATA-TABLE', 'Field');
    }

    public function getMetadataResources($resource)
    {
        $params = array(
            'ID' => $resource,
        );

        return $this->getMetadata($params, 'METADATA-RESOURCE', 'Resource');
    }

    /**
     * @param type $resource
     * @return \PHRETS\Result\Result
     */
    public function getMetadataClasses($resource)
    {
        $params = array(
            'ID' => $resource,
        );

        return $this->getMetadata($params, 'METADATA-CLASS', 'Class');
    }

    /**
     * Keeping the Metadata requests DRY
     * 
     * @param \PHRETS\Response\XmlResponse $response
     * @param type $data_node
     * @param type $node
     * @return \PHRETS\Result\Result
     */
    private function getMetadata($params, $data_node, $node)
    {
        $defaults = array(
            'Type' => $data_node,
            'Format' => 'STANDARD-XML',
        );

        $params   = array_replace($defaults, $params);
        $response = $this->client->request('GetMetadata', $params);
        $result   = new Result($response);
        $body     = $response->getBody();

        if (isset($body->METADATA, $body->METADATA->{$data_node})) {
            $data_node = $body->METADATA->{$data_node};

            foreach ($data_node as $data) {
                $properties = $data->attributes();

                $metadata_result = new MetadataResult();
                $metadata_result->setProperties($properties);

                if (is_array($node)) {
                    $values = array();

                    foreach ($node as $try_node) {
                        if (isset($data->{$try_node})) {
                            $values = $data->{$try_node};
                            break;
                        }
                    }
                } else {
                    $values = $data->{$node};
                }

                $metadata_result->setResults($values);
                $result->addResult($metadata_result);
            }
        }

        return $result;
    }
}