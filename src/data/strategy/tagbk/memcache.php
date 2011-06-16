<?php

/*
 * class Cache_Tag_Backend_Memcache
 * 
 */

class Cache_Tag_Backend_Memcache  implements Cache_Tag_Backend{
    
    private static $memcache=null;

    const NAME    = 'Memcache';
    
    function __construct() {
        self::$memcache = Mcache::init();
    }

    /*
     * Очистка кеша по тегу
     * function clearTag
     * @param $tagKey string
     */
    function clearTag($tagKey){
        return self::$memcache->set($tagKey, time(), false, 0 );
    }
    
}

?>