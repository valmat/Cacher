<?php

/*
 * class Cache_Tag_Backend_MemReCache
 * 
 */

class Cache_Tag_Backend_MemReCache implements Cache_Tag_Backend {
    
    private static $memcache=null;
    
    function __construct() {
        self::$memcache = Mcache::init();
    }
    
    function clearTag($tagKey){
        self::$memcache->set($tagKey, time(), false, 0 );
    }

}

?>