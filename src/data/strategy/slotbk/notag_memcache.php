<?php

/*
 * class Cacher_Backend_notag_Memcache
 * Бэкенд класса Cacher для кеширования в memcache
 *
 * В этом бекенде теги не поддерживаются. По суи простая обертка для memcache
 * 
 */

class Cacher_Backend_notag_Memcache  implements Cacher_Backend{
    
    private static $memstore = NULL;
    private $key;
       
    function __construct($CacheKey) {
        $this->key  = $CacheKey;
        self::$memstore = Memstore::init();
    }

    /*
     * Получение кеша
     * function get
     */
    public function get(){
        # если объекта в кеше не нашлось
        if( false===($cobj = self::$memstore->get($this->key)) )
           return false;
        
        return $cobj;
    }
    
    /*
     * Получение кеша для мультиключа
     * function get
     */
    static function multiGet($keys){
        !self::$memstore && (self::$memstore = Memstore::init());
        # Если объекта в кеше не нашлось, то безусловно перекешируем
        if( false===( $Cobjs = self::$memstore->get( $keys )) ){
            return false;
        }
        
        $rekeys = array_flip($keys);
        $rez = array_fill_keys($rekeys, false);
        foreach($Cobjs as $rekey => $cobj) {
            $rez[$rekeys[$rekey]] = $cobj;
        }
        
        return $rez;
    }
    
    /*
     * Установка значения кеша по ключу вместе с тегами и указанием срока годности кеша
     * function set
     * @param $CacheVal string
     * @param $tags array
     * @param $LifeTime int
     */
    function set($CacheVal, $tags, $LifeTime){
        self::$memstore->set($this->key, $CacheVal, $LifeTime);
        return $CacheVal;
    }
    
    /*
     * Удаление кеша по собственному ключу
     * function del
     */
    function del(){
        return self::$memstore->del($this->key);
    }
    
    /*
     * tagsType()
     * @param void
     * @return string Cache tag type throw CacheTagTypes namespace
     */
    function tagsType() {
        return CacheTagTypes::NOTAG;
    }
    
    /*
     * Возвращает объект блокировки и спользуемой в этом бэкенде.
     * Либо false, если блокировки не используются
     * @param void
     * @return false
     */
    public function lock() {
        return false;
    }
    
    /*
     * Возвращает ключ
     * @param void
     * @return string
     */
    public function getKey() {
        return $this->key;
    }
    
}

