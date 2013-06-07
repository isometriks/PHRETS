<?php

namespace PHRETS\Event; 

class Events
{
    const PRE_CONNECT = 'phrets.pre_connect'; 
    const POST_CONNECT = 'phrets.post_connect';
    
    const PRE_REQUEST = 'phrets.pre_request'; 
    const POST_REQUEST = 'phrets.post_request'; 
    
    const PRE_DISCONNECT = 'phrets.pre_disconnect'; 
    const POST_DISCONNECT = 'phrets.post_disconnect'; 
}