<?php

/*
 * class Cacher_Backend_MemReCache
 * 
 */

class Cacher_Lock_Memcache implements Cacher_Lock {
    
    protected static $memcache=null;
    
    /**
      * Префикс для формирования ключа блокировки
      */
    const LOCK_PREF = CONFIG_Cacher_Lock_Memcache::LOCK_PREF;
    /**
      * Время жизни ключа блокировки. Если во время перестроения кеша процесс аварийно завершится,
      * то блокировка останется включенной и другие процессы будут продолжать выдавать протухший кеш LOCK_TIME секунд.
      * С другой стороны если срок блокировки истечет до того, как кеш будет перестроен, то возникнет состояние гонки и блокировочный механизм перестанет работать.
      * Т.е. LOCK_TIME нужно устанавливать таким, что бы кеш точно успел быть построен, и не слишком больши, что бы протухание кеша было заметно в выдаче клиенту
      */
    const LOCK_TIME = CONFIG_Cacher_Lock_Memcache::LOCK_TIME;
    
    /**
      * Флаг установленной блокировки
      * После установки этот флаг помечается в true
      * В методе set проверяется данный флаг, и только если он установлен, тогда снимается блокировка [self::$memcache->delete(self::LOCK_PREF . $CacheKey)]
      * Затем флаг блокировки должен быть снят: self::$locked[$key] = false;
      */
    private static $locked = array();
    
    /*
     * проверяем не установил ли кто либо блокировку
     * Если блокировка не установлена, пытаемся создать ее методом add, что бы предотвратить состояние гонки
     * function set_lock
     * @param $arg void
     */
    static function set($key) {
        !self::$memcache && ( self::$memcache = Mcache::init() );
        self::$locked[$key] = isset(self::$locked[$key]) && self::$locked[$key];
        if( !(self::$locked[$key]) && !(self::$memcache->get(self::LOCK_PREF . $key)) )
            self::$locked[$key] = self::$memcache->add(self::LOCK_PREF . $key,true,false,self::LOCK_TIME);
        return self::$locked[$key];
    }
    
    /*
     * проверяем не установил ли кто либо блокировку
     * Если блокировка не установлена, пытаемся создать ее методом add, что бы предотвратить состояние гонки
     * function set_lock
     * @param $arg void
     */
    static function del($key) {
        !self::$memcache && ( self::$memcache = Mcache::init() );
        if(isset(self::$locked[$key]) && self::$locked[$key] && self::$memcache->delete(self::LOCK_PREF . $key, 0)) {
            unset(self::$locked[$key]);
            return true;
        }
        return false;
    }
    
}

?>