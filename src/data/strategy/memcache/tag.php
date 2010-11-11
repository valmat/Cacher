<?php

/*
 * class Cache_Tag_Backend_Memcache
 * 
 */

class Cacher_Backend_Memcache  implements Cacher_Backend{
    
    private static $memcache=null;

    const NAME    = 'Memcache';
    
    function __construct() {
        if(null==self::$memcache){
           self::$memcache = Mcache::init();
        }
    }

    /*
     * Очистка кеша по тегу
     * function clearTag
     * @param $tagKey string
     */
    function clearTag($tagKey){
        self::$memcache->set($tagKey, time(), false, 0 );
    }
    
}

?>