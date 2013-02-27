<?php

namespace PHRETS\Result;

class SearchResult extends AbstractResult implements \IteratorAggregate
{
    protected $column_names;
    protected $delimiter;
    protected $results;

    public function getColumnNames()
    {
        return $this->column_names;
    }

    public function setColumnNames($column_names)
    {
        $this->column_names = $column_names;
    }

    public function getDelimiter()
    {
        return $this->delimiter;
    }

    public function setDelimiter($delimiter)
    {
        $this->delimiter = $delimiter;
    }

    public function getResults()
    {
        return $this->results;
    }

    public function setResults($results)
    {
        $this->results = $results;
    }

    public function getIterator()
    {
        return new \ArrayIterator($this->getResults());
    }
}