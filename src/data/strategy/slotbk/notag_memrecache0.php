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

require_once CONFIG_Cacher::PATH_BACKENDS . 'locks/lock.memstore.php';

class Cacher_Backend_notag_MemReCache0 implements Cacher_Backend{
    
    /**
      * MAX_LifeTIME - максимальное время жизни кеша. По умолчанию 29 дней. Если методу set передан $LifeTime=0, то будет установлено 'expire' => (time()+self::MAX_LTIME)
      */
    const MAX_LTIME = CONFIG_Cacher_BK_MemReCache0::MAX_LTIME;
    
    private static $memstore = NULL;
    private $key;
    
    function __construct($CacheKey) {
        $this->key  = $CacheKey;
        self::$memstore = Memstore::init();
    }
    
    public function get() {
        # если объекта в кеше не нашлось, то безусловно перекешируем
        if( false===($cobj = self::$memstore->get($this->key)) || !isset($cobj[0]) || !isset($cobj[1]) )
            return false;
        list($rez, $expire) = $cobj;
        
        
        # Если время жизни кеша истекло, то перекешируем с условием блокировки
        # Пытаемся установить блокировку
        # Если блокировку установили мы, то отправляемся перекешировать, иначе возвращаем устаревший объект из кеша
        if($expire < time() && self::lock()->set($this->key)){
            return false;
        }
        return $rez;
    }
    
    /*
     * Получение кеша для мультиключа
     * function get
     */
    static function multiGet($keys){
        !self::$memstore && (self::$memstore = Memstore::init());
        
        # если объекта в кеше не нашлось, то безусловно перекешируем
        if( false===($cobj = self::$memstore->get($keys)))
           return false;
        
        $rez = array();
        foreach($keys as $id => $key) {
            if(!isset($cobj[$key])) {
                $rez[$id] = false;
                continue;
            }
            list($obj, $expire) = $cobj[$key];
            if($expire < time() && self::lock()->set($key)){
                $rez[$id] = false;
                continue;
            }
            $rez[$id] = $obj;
        }
        return $rez;
    }
    
    /*
     * Установка значения кеша по ключу вместе с тегами и указанием срока годности кеша
     * Проверяется установка блокировки
     * function set
     * @param $CacheVal string, $tags array, $LifeTime int
     */
    public function set($CacheVal, $tags, $LifeTime){
        $thetime = time();
        $cobj = Array(
                      0 => $CacheVal,
                      1 => (((0==$LifeTime)?(self::MAX_LTIME):$LifeTime)+$thetime)
                     );
        self::$memstore->set($this->key, $cobj);
        return $CacheVal;
    }
    
    /*
     * Полная очистка текущего кеша без поддержки переекеширования.
     * Удаление с перекешированием в этом слоте не поддерживается
     * function del
     */
    public function del(){
        return self::$memstore->del($this->key);
    }
    
    /*
     * tagsType()
     * @param void
     * @return string Cache tag type throw CacheTagTypes namespace
     */
    public function tagsType() {
        return CacheTagTypes::NOTAG;
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

