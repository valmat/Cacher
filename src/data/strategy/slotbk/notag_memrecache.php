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

require_once CONFIG_Cacher::PATH_BACKENDS . 'locks/lock.memstore.php';

class Cacher_Backend_notag_MemReCache implements Cacher_Backend {
    
    /**
      * MAX_LifeTIME - максимальное время жизни кеша. По умолчанию 29 дней. Если методу set передан $LifeTime=0, то будет установлено 'expire' => (time()+self::MAX_LTIME)
      */
    const MAX_LTIME = CONFIG_Cacher_BK_MemReCache::MAX_LTIME;
    /**
      * EXPIRE PREFIX - префикс для хранения ключа со временем истечения кеша
      */
    const EXPR_PREF = CONFIG_Cacher_BK_MemReCache::EXPR_PREF;
    
    private static $memstore = NULL;
    private $key;
    
    function __construct($CacheKey) {
        $this->key  = $CacheKey;
        self::$memstore = Memstore::init();
    }
    
    public function get() {
        # Если объекта в кеше не нашлось, то безусловно перекешируем
        if( false===( $c_arr = self::$memstore->get(array($this->key, self::EXPR_PREF . $this->key)) ) ) {
           return false;
        }
        if(!isset($c_arr[$this->key])) {
            return false;
        }
        
        # Если время жизни кеша истекло, то перекешируем с условием блокировки
        if( !isset($c_arr[self::EXPR_PREF . $this->key]) || $c_arr[self::EXPR_PREF . $this->key] < time() ){
            # Пытаемся установить блокировку
            # Если блокировку установили мы, то отправляемся перекешировать, иначе возвращаем устаревший объект из кеша
            if(self::lock()->set($this->key))
                return false;
        }
        return $c_arr[$this->key];
    }
    
    /*
     * Получение кеша для мультиключа
     * function get
     */
    static function multiGet($keys) {
        !self::$memstore && (self::$memstore = Memstore::init());
        $expir_keys  = array_map ( 'self::expirKey' , $keys );
        # Если объекта в кеше не нашлось, то безусловно перекешируем
        if( false===( $c_arr = self::$memstore->get( array_merge ( $expir_keys, $keys ) )) ){
            return false;
        }
        
        $rez = array();
        foreach($keys as $k => $key) {
            if(!isset($c_arr[$key])) {
                $rez[$k] = false;
                continue;
            }
            # Если время жизни кеша истекло, то перекешируем с условием блокировки
            if( !isset($c_arr[self::EXPR_PREF . $key]) || $c_arr[self::EXPR_PREF . $key] < time() ){
                # Пытаемся установить блокировку
                # Если блокировку установили мы, то отправляемся перекешировать, иначе возвращаем устаревший объект из кеша
                if(self::lock()->set($key)) {
                    $rez[$k] = false;
                    continue;
                }
            }
            $rez[$k] = $c_arr[$key];
        }
        return $rez;
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
        
        self::$memstore->set($this->key, $CacheVal);
        self::$memstore->set(self::EXPR_PREF.$this->key, $expire);
        
        return $CacheVal;
    }
    
    /*
     * Сброс времени жизни кеша, дабы вызывать перекеширование
     * Поддерживается удаление с перекешированием
     * function del
     */
    function del(){
        return self::$memstore->del(self::EXPR_PREF . $this->key);
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
     * function expirKey
     * @param $key
     */
    private static function expirKey($key) {
        return self::EXPR_PREF . $key;
    }
    
    /*
     * Возвращает объект блокировки и спользуемой в этом бэкенде.
     * Либо false, если блокировки не используются
     * @param void
     * @return Cacher_Lock object or false
     */
    public function lock() {
        return Cacher_Lock_Memstore::init();
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
