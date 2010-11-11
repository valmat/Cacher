<?php

/*
 * class Cache_Tag_Backend_MemReFile
 * 
 */

class Cache_Tag_Backend_MemReFile implements Cache_Tag_Backend {
    
    private static $memcache=null;
    
    const NAME      = 'MemReFile';

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