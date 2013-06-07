# PHRETS

A simple, free, open source PHP library for using [RETS](http://rets.org).

PHP + RETS = PHRETS


## Introduction

PHRETS provides PHP developers a way to integrate RETS functionality directly within new or existing code. A standard class of functions is made available to developers to connect and interact with a server much like they would with other APIs.

PHRETS handles the following aspects of RETS communication for you:
* Response parsing (for other non-XML responses such as HTTP multipart)
* XML Parsing
* Simple variables and arrays returned to the developer
* RETS communication (over HTTP)
* HTTP Header management
* Authentication
* Session/Cookie management


## Download

**Install via Composer** - Add [troydavisson/phrets](https://packagist.org/packages/troydavisson/phrets) to your `composer.json` file, run `composer update` and you're set.  
**Manual Download** - The source code for PHRETS is available on [GitHub](http://github.com/troydavisson/PHRETS)


## Usage

    $client = new CurlClient(); 
    $client->connect('http://rets.site.com', 'username', 'password'); 

    $rets = new phRETS($client); 

    /**
     * Running a search
     */
    $result = $rets->search('Resource', 'Class', '(5=200)', array('Limit' => 10)); 

    $columns = $result->getColumnNames(); 
    $results = $result->getResults(); 

    foreach($result as $row){
        foreach($row as $column => $value){
            // Column names match RETS columns

        }
    }

    /**
     * Get media objects
     */
    $result = $rets->getObject('Property', 'Photo', array(
        '3791261'  => '*', 
        '3867614'  => array(1,2,5), 
    )); 

    foreach($result as $object){
        if($object instanceof \PHRETS\Result\Object){

            $dir = __DIR__ . '/' . $object->getContentId(); 

            if(!is_dir($dir)){
                mkdir($dir); 
            }

            // Writes {content-id}-{object-id}.{extension}
            // use $object->getFilename() to get the name. 
            $object->write($dir);

        } else {
            echo 'Error: ' . $object->getError() . "\n"; 
        }
    }

    /**
     * Get Single Object
     */
    $result = $rets->getObject('Property', 'Photo', array(
        '12345' => 1,
    )); 

    $object = $result->getSingleResult(); 
    $object->write(__DIR__); 
     

## Contribute

PHRETS is maintained in a public Git repository on GitHub.  Issue submissions and pull requests are encouraged if you run into issues or if you have fixes or changes to contribute.

## Documentation

View our [GitHub Wiki](https://github.com/troydavisson/PHRETS/wiki) for documentation, code snippets, examples, tips & tricks and more.
