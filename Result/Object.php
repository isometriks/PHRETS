<?php

namespace PHRETS\Result; 

use PHRETS\Response\Response;

class Object
{
    private $type; 
    private $data; 
    private $content_id; 
    private $object_id; 
    
    public static $types = array(
        'image/jpg'  => 'jpg', 
        'image/jpeg' => 'jpg',
        'image/gif'  => 'gif', 
        'image/png'  => 'png', 
        'image/tiff' => 'tiff', 
    ); 
    
    public function __construct(Response $response)
    {
        $this->type = $response->getHeader('Content-Type'); 
        $this->content_id = $response->getHeader('Content-ID'); 
        $this->object_id = $response->getHeader('Object-ID'); 
        $this->data = $response->getBody(); 
    }
    
    public function getContentId()
    {
        return $this->content_id; 
    }
    
    public function getObjectId()
    {
        return $this->object_id; 
    }
    
    public function getFilename()
    {
        return sprintf('%d-%d.%s', $this->getContentId(), $this->getObjectId(), self::$types[$this->type]); 
    }
    
    public function write($dir, $filename = null)
    {
        if($filename === null){
            $filename = $this->getFilename(); 
        }
        
        file_put_contents($dir.'/'.$filename, $this->data); 
    }
}