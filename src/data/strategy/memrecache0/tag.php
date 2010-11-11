<?php

/*
 * class Cache_Tag_Backend_MemReCache0
 * 
 */

class Cache_Tag_Backend_MemReCache0 implements Cache_Tag_Backend {
    
    private static $memcache=null;
    
    const NAME      = 'MemReCache0';
    
    function __construct() {
        if(null==self::$memcache){
           self::$memcache = Mcache::init();
        }
    }
    
    function clearTag($tagKey){
        self::$memcache->set($tagKey, time(), false, 0 );
    }
    
    
}

?>