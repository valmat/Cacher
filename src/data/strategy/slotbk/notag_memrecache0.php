<?php

/*
 * class Cacher_Backend_notag_MemReCache0
 *
 * В этом бекенде ТЕГИ НЕ ПОДДЕРЖИВАЮТСЯ.
 * Если нужны теги, используйте Cacher_Backend_MemReCache0
 *
 * В отличии от Cacher_Backend_notag_MemReCache, этот слот больше расчитан на
 * безгоночное перекеширование при протухании чем при удалении кеша
 * 
 * Бэкенд класса Cacher для кеширования в memcache c безопасным перекешированием.
 * Для перекеширования используются блокировки.
 * Таким образом исключается состаяние гонки и обновлением кеша занимается только один процесс.
 * Тем временем другие процессы временно используют устаревший кеш.
 * К кешируемому объекту добавляется параметр 'expire'. таким образом за истечением срока годности кеша должен следить не memcache,
 * а сам класс.
 * В данном бекенде удаление по ключу происходит безусловно. То есть. после вызова del перекеширование не возможно.
 * 
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 *  CacheObj:
 *  'cache_key'=Array(
 *      0 => <cached data>
 *      1 => <expire>
 *                  );
 * LockFlag:
 * '~lock'.'cache_key' = true/false
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * 
 */

class Cacher_Backend_notag_MemReCache0 extends Cacher_Backend{
    
    private static $memcache=null;
    
    const NAME      = 'notag_MemReCache0';
    
    /**
      * сжатие memcache
      */
    const COMPRES   = false;//MEMCACHE_COMPRESSED;
    
    /**
      * Префикс для формирования ключа блокировки
      */
    const LOCK_PREF = CONFIG_Cacher_BK_MemReCache0::LOCK_PREF;
    
    /**
      * Время жизни ключа блокировки. Если во время перестроения кеша процесс аварийно завершится,
      * то блокировка останется включенной и другие процессы будут продолжать выдавать протухший кеш LOCK_TIME секунд.
      * С другой стороны если срок блокировки истечет до того, как кеш будет перестроен, то возникнет состояние гонки и блокировочный механизм перестанет работать.
      * Т.е. LOCK_TIME нужно устанавливать таким, что бы кеш точно успел быть построен, и не слишком больши, что бы протухание кеша было заметно в выдаче клиенту
      */
    const LOCK_TIME = CONFIG_Cacher_BK_MemReCache0::LOCK_TIME;
    
    /**
      * MAX_LifeTIME - максимальное время жизни кеша. По умолчанию 29 дней. Если методу set передан $LifeTime=0, то будет установлено 'expire' => (time()+self::MAX_LTIME)
      */
    const MAX_LTIME = CONFIG_Cacher_BK_MemReCache0::MAX_LTIME;
    
    /**
      * Флаг установленной блокировки
      * После установки этот флаг помечается в true
      * В методе set проверяется данный флаг, и только если он установлен, тогда снимается блокировка [self::$memcache->delete(self::LOCK_PREF . $this->key)]
      * Затем флаг блокировки должен быть снят: $this->is_locked = false;
      */
    private        $is_locked = false;
    
    function __construct($CacheKey, $nameSpace) {
        parent::__construct($CacheKey, $nameSpace);
        $this->key = $nameSpace . $CacheKey;
        self::$memcache = Mcache::init();
    }
    
    /*
     * проверяем не установил ли кто либо блокировку
     * Если блокировка не установлена, пытаемся создать ее методом add, что бы предотвратить состояние гонки
     * function set_lock
     * @param $arg void
     */
    private function set_lock() {
        if( !($this->is_locked) && !(self::$memcache->get(self::LOCK_PREF . $this->key)) )
           $this->is_locked = self::$memcache->add(self::LOCK_PREF . $this->key, true, false, self::LOCK_TIME);
        return $this->is_locked;
    }
    
    function get(){
        # если объекта в кеше не нашлось, то безусловно перекешируем
        if( false===($cobj = self::$memcache->get($this->key)) || !isset($cobj[0]) || !isset($cobj[1]) )
           return false;
        list($rez, $expire) = $cobj;
        
        # Если время жизни кеша истекло, то перекешируем с условием блокировки
        # Пытаемся установить блокировку
        # Если блокировку установили мы, то отправляемся перекешировать, иначе возвращаем устаревший объект из кеша
        if($expire < time() && $this->set_lock()){
          return false;
        }
        return $rez;
    }
    
    /*
     * Установка значения кеша по ключу вместе с тегами и указанием срока годности кеша
     * Проверяется установка блокировки
     * function set
     * @param $CacheVal string, $tags array, $LifeTime int
     */
    function set($CacheVal, $tags, $LifeTime){
        $thetime = time();
        
        $cobj = Array(
                      0 => $CacheVal,
                      1 => (((0==$LifeTime)?(self::MAX_LTIME):$LifeTime)+$thetime)
                     );
        self::$memcache->set($this->key, $cobj, self::COMPRES, 0);
        
        # Сбрасываем блокировку
        if($this->is_locked){
            $this->is_locked = false;
            self::$memcache->delete(self::LOCK_PREF . $this->key, 0);
        }
        
        return $CacheVal;

    }
    
    /*
     * Полная очистка текущего кеша без поддержки переекеширования.
     * Удаление с перекешированием в этом слоте не поддерживается
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