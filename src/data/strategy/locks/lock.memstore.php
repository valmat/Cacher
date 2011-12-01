<?php

/*
 * class Cacher_Backend_MemReCache
 * 
 */

class Cacher_Lock_Memstore extends Cacher_Lock {
    
    protected static $memstore = NULL;
    
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
    
    /*
     * function set
     * проверяем не установил ли кто либо блокировку
     * Если блокировка не установлена, пытаемся создать ее методом add, что бы предотвратить состояние гонки
     * @param $key string
     * @return bool
     */
    public function set($key) {
        !self::$memstore && ( self::$memstore = Memstore::init() );
        self::$locked[$key] = isset(self::$locked[$key]) && self::$locked[$key];
        if( !(self::$locked[$key]) && !(self::$memstore->get(self::LOCK_PREF . $key)) )
            self::$locked[$key] = self::$memstore->add(self::LOCK_PREF . $key,1,self::LOCK_TIME);
        return self::$locked[$key];
    }
    
    /*
     * function del
     * Удаление блокировки
     * @param $key string
     * @return bool
     */
    public function del($key) {
        !self::$memstore && ( self::$memstore = Memstore::init() );
        if(isset(self::$locked[$key]) && self::$locked[$key] && self::$memstore->del(self::LOCK_PREF . $key)) {
            unset(self::$locked[$key]);
            return true;
        }
        return false;
    }
    
    /*
     * function get
     * Проверка установленности блокировки
     * @param $key string
     * @return bool
     */
    public function get($key) {
        return isset(self::$locked[$key]) && self::$locked[$key];
    }
    
}
