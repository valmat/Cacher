<?php

/*
 * class Cacher_Backend_notag_Memcache
 * Бэкенд класса Cacher для кеширования в memcache
 *
 * В этом бекенде теги не поддерживаются. По суи простая обертка для memcache
 * 
 */
class Cacher_Backend_notag_Memcache  extends Cacher_Backend{
    
    private static $memcache=null;
       
    function __construct($CacheKey) {
        parent::__construct($CacheKey);
        self::$memcache = Mcache::init();
    }

    /*
     * Получение кеша
     * function get
     */
    protected function singleGet(){
        # если объекта в кеше не нашлось
        if( false===($cobj = self::$memcache->get($this->key)) )
           return false;
        
        return $cobj;
    }
    
    /*
     * Получение кеша для мультиключа
     * function get
     */
    protected function multiGet(){
        #
        echo "<hr>{{{<pre>";
        var_export($this->key);
        echo '</pre>}}}<hr>';
        return array();
    }
    
    /*
     * Установка значения кеша по ключу вместе с тегами и указанием срока годности кеша
     * function set
     * @param $CacheVal string
     * @param $tags array
     * @param $LifeTime int
     */
    function set($CacheVal, $tags, $LifeTime){
        self::$memcache->set($this->key, $CacheVal, Mcache::COMPRES, $LifeTime);
        return $CacheVal;
    }
    
    /*
     * Удаление кеша по собственному ключу
     * function del
     */
    function del(){
        return self::$memcache->delete($this->key, 0);
    }
    
    /*
     * tagsType()
     * @param void
     * @return string Cache tag type throw CacheTagTypes namespace
     */
    function tagsType() {
        return CacheTagTypes::NOTAG;
    }    
}

?>