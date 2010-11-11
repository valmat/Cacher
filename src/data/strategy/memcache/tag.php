<?php

/*
 * class Cache_Tag_Backend_Memcache
 * 
 */

class Cache_Tag_Backend_Memcache  implements Cache_Tag_Backend{
    
    private static $memcache=null;

    const NAME    = 'Memcache';
    
    function __construct() {
        if(null==self::$memcache){
           self::$memcache = Mcache::init();
        }
    }

    /*
     * ������� ���� �� ����
     * function clearTag
     * @param $tagKey string
     */
    function clearTag($tagKey){
        self::$memcache->set($tagKey, time(), false, 0 );
    }
    
}

?>