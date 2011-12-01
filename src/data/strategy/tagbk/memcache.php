<?php

/*
 * class Cache_Tag_Backend_Memcache
 * 
 */

class Cache_Tag_Backend_Memcache  implements Cache_Tag_Backend{
    
    private static $memstore = NULL;

    function __construct() {
        //self::$memstore = Memstore::init();
    }

    /*
     * Очистка кеша по тегу
     * function clearTag
     * @param $tagKey string
     */
    function clearTag($tagKey){
        //return self::$memstore->set($tagKey, time());
        //return self::$memstore->del($tagKey);
        return Memstore::init()->del($tagKey);
    }
    
}

?>