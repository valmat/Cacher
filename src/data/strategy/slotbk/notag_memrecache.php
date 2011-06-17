<?php

/*
 * class Cacher_Backend_notag_MemReCache
 *
 * В этом бекенде ТЕГИ НЕ ПОДДЕРЖИВАЮТСЯ.
 * Если нужны теги, используйте Cacher_Backend_MemReCache
 * 
 * 
 * В отличии от Cacher_Backend_notag_MemReCache0, этот слот больше расчитан на
 * безгоночное перекеширование при удалении чем при  кеша протухании
 * 
 * 
 * Бэкенд класса Cacher для кеширования в memcache c безопасным перекешированием.
 * Для перекеширования используются блокировки.
 * Таким образом исключается состаяние гонки и обновлением кеша занимается только один процесс.
 * Тем временем другие процессы временно используют устаревший кеш.
 * К кешируемому объекту добавляется параметр 'expire'.
 * таким образом за истечением срока годности кеша должен следить не memcache,
 * а сам класс.
 * В отличии от Cacher_Backend_MemReCache0, в данном классе при удалении кеша по ключу
 * происходит не фактическое удаление кеша на уровне Memcache,
 * а лишь сброс параметра Expire.
 * За счет этого возможен сброс кеша без использования тегов с возможностью безгоночного перекеширования
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 *  CacheObj:
 *  'cache_key'=<cached data>
 * LockFlag:
 * '~lock'.'cache_key' = true/false
 * Expire:
 * '~xpr'.'cache_key' = ...
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * 
 */

class Cacher_Backend_notag_MemReCache extends Cacher_Backend {
    
    private static $memcache=null;
    
    const NAME      = 'notag_MemReCache';
    /**
      * сжатие memcache
      */
    const COMPRES   = false;//MEMCACHE_COMPRESSED;
    
    /**
      * Префикс для формирования ключа блокировки
      */
    const LOCK_PREF = CONFIG_Cacher_BK_MemReCache::LOCK_PREF;
    /**
      * Время жизни ключа блокировки. Если во время перестроения кеша процесс аварийно завершится,
      * то блокировка останется включенной и другие процессы будут продолжать выдавать протухший кеш LOCK_TIME секунд.
      * С другой стороны если срок блокировки истечет до того, как кеш будет перестроен, то возникнет состояние гонки и блокировочный механизм перестанет работать.
      * Т.е. LOCK_TIME нужно устанавливать таким, что бы кеш точно успел быть построен, и не слишком больши, что бы протухание кеша было заметно в выдаче клиенту
      */
    const LOCK_TIME = CONFIG_Cacher_BK_MemReCache::LOCK_TIME;
    /**
      * MAX_LifeTIME - максимальное время жизни кеша. По умолчанию 29 дней. Если методу set передан $LifeTime=0, то будет установлено 'expire' => (time()+self::MAX_LTIME)
      */
    const MAX_LTIME = CONFIG_Cacher_BK_MemReCache::MAX_LTIME;
    /**
      * EXPIRE PREFIX - префикс для хранения ключа со временем истечения кеша
      */
    const EXPR_PREF = CONFIG_Cacher_BK_MemReCache::EXPR_PREF;
    
    /**
      * Флаг установленной блокировки
      * После установки этот флаг помечается в true
      * В методе set проверяется данный флаг, и только если он установлен, тогда снимается блокировка [self::$memcache->delete(self::LOCK_PREF . $CacheKey)]
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
           $this->is_locked = self::$memcache->add(self::LOCK_PREF . $this->key,true,false,self::LOCK_TIME);
        return $this->is_locked;
    }
    
    protected function singleGet() {
        # Если объекта в кеше не нашлось, то безусловно перекешируем
        if( false===( $rez = self::$memcache->get($this->key) ) ){
           return false;
        }
        
        # Если время жизни кеша истекло, то перекешируем с условием блокировки
        if( false===( $expire = self::$memcache->get(self::EXPR_PREF . $this->key) ) || $expire < time() ){
          # Пытаемся установить блокировку
          # Если блокировку установили мы, то отправляемся перекешировать, иначе возвращаем устаревший объект из кеша
          if($this->set_lock())
            return false;
        }
        return $rez;
    }
    
    /*
     * Получение кеша для мультиключа
     * function get
     */
    protected function multiGet() {
        #
    }
    
    /*
     * Установка значения кеша по ключу вместе указанием срока годности кеша
     * Проверяется установка блокировки
     * function set
     * @param $CacheVal string, $tags array, $LifeTime int
     */
    function set($CacheVal, $tags, $LifeTime){
        $thetime = time();
        $expire = (((0==$LifeTime)?(self::MAX_LTIME):$LifeTime)+$thetime);
        
        self::$memcache->set($this->key, $CacheVal, self::COMPRES, 0);
        self::$memcache->set(self::EXPR_PREF.$this->key, $expire, false, 0);
        
        # Сбрасываем блокировку
        if($this->is_locked){
            $this->is_locked = false;
            self::$memcache->delete(self::LOCK_PREF . $this->key, 0);
        }
        return $CacheVal;
    }
    
    /*
     * Сброс времени жизни кеша, дабы вызывать перекеширование
     * Поддерживается удаление с перекешированием
     * function del
     */
    function del(){
        return self::$memcache->delete(self::EXPR_PREF . $this->key, 0);
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