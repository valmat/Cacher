<?php

/**
 * Интерфейс блокировки для бэкэнда для кеширования.
 * 
 */

abstract class Cacher_Lock {
    
    /**
      * Флаг установленной блокировки
      * После установки этот флаг помечается в true
      * В методе set проверяется данный флаг, и только если он установлен, тогда снимается блокировка [self::$memcache->delete(self::LOCK_PREF . $CacheKey)]
      * Затем флаг блокировки должен быть снят: self::$locked[$key] = false;
      */
    protected static $lock = NULL;
    
    /**
      * Флаг установленной блокировки
      * После установки этот флаг помечается в true
      * В методе set проверяется данный флаг, и только если он установлен, тогда снимается блокировка [self::$memcache->delete(self::LOCK_PREF . $CacheKey)]
      * Затем флаг блокировки должен быть снят: self::$locked[$key] = false;
      */
    protected static $locked = array();
    
    static function init(){
        if(NULL===self::$lock){
           self::$lock = new static;
        }
        return self::$lock;
    }
    
    /*
     * function set
     * проверяем не установил ли кто либо блокировку
     * Если блокировка не установлена, пытаемся создать ее методом add, что бы предотвратить состояние гонки
     * @param $key string
     * @return bool
     */
    abstract public function set($key);
    
    /*
     * function del
     * Удаление блокировки
     * @param $key string
     * @return bool
     */
    abstract public function del($key);
    
    /*
     * function get
     * Проверка установленности блокировки
     * @param $key string
     * @return bool
     */
    abstract public function get($key);
    
}

