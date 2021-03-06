<?php

/*
 * class Cacher_Backend_MemReCache
 * 
 * Бэкенд класса Cacher для кеширования в memcache c безопасным перекешированием.
 * Для перекеширования используются блокировки.
 * Таким образом исключается состаяние гонки и обновлением кеша занимается только один процесс.
 * Тем временем другие процессы временно используют устаревший кеш.
 * Как и в Cacher_Backend_Memcache используются теги.
 * К кешируемому объекту добавляется параметр 'expire'. таким образом за истечением срока годности кеша должен следить не memcache,
 * а сам класс.
 * В отличии от Cacher_Backend_MemReCache0 в данном классе при удалении кеша по ключу происходит не фактическое удаление кеша на уравне
 * Memcache, а лишь сброс параметра Expire. За счет этого возможен сброс кеша без использования тегов с возможностью безгоночного перекеширования
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 *  CacheObj:
 *  'cache_key'=Array(
 *        'data' => ...
 *        'tags' => Array(
 *                       'tag1' => ...,
 *                       'tag2' => ...,
 *                       'tag3' => ...
 *                       )
 *                  );
 * LockFlag:
 * '~lock'.'cache_key' = true/false
 * Expire:
 * '~xpr'.'cache_key' = ...
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * 
 */

require_once CONFIG_Cacher::PATH_BACKENDS . 'locks/lock.memstore.php';

class Cacher_Backend_MemReCache implements Cacher_Backend  {
    
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
        if( false===( $c_arr = self::$memstore->get( array( $this->key, self::EXPR_PREF . $this->key ) )) ){
            return false;
        }
        return self::mainGet($this->key, $c_arr);
    }
    
    /*
     * Получение кеша для мультиключа
     * function get
     */
    static function multiGet($keys){
        !self::$memstore && (self::$memstore = Memstore::init());
        $expir_keys  = array_map ( 'self::expirKey' , $keys );
        # Если объекта в кеше не нашлось, то безусловно перекешируем
        if( false===( $c_arr = self::$memstore->get( array_merge ( $expir_keys, $keys ) )) ){
            return false;
        }
        
        $rez = array();
        foreach($keys as $k => $key) {
            $rez[$k] = self::mainGet($key, $c_arr);
        }
        return $rez;
    }
        
    /*
     * function mainGet
     * @param $key string
     * @param $c_arr array
     */
    private static function mainGet($key, &$c_arr) {
        # Если объекта в кеше не нашлось, то безусловно перекешируем
        if(!isset($c_arr[$key]) || !isset($c_arr[self::EXPR_PREF . $key]) ){
            return false;
        }
        
        $cobj   = $c_arr[$key];
        $expire = $c_arr[self::EXPR_PREF . $key];
        
        # Если время жизни кеша истекло, то перекешируем с условием блокировки
        $lock = self::lock();
        if( false===$expire || $expire < time() ){
            # Пытаемся установить блокировку
            # Если блокировку установили мы, то отправляемся перекешировать, иначе возвращаем устаревший объект из кеша
            if($lock->set($key)) {
                return false;
            }
            return $cobj['data'];
        }
        $tags = $cobj['tags'];
        $tags_cnt = count($tags);
        
        # Если тегов нет, то просто отдаем объект. Тогда дальше можно считать 0!=$tags_cnt
        if(0==$tags_cnt)
            return $cobj['data'];
        
        $tags_mc = self::$memstore->get( array_keys($cobj['tags']) );
        # Если в кеше утеряна информация о каком либо теге, то сбрасывается кеш ассоциированный с этим тегом
        if( count($tags_mc)!= $tags_cnt) {
            if($lock->set($key)) {
                return false;
            }
            return $cobj['data'];        
        }
        
        # Если кеш протух по тегам, то сообщаем об этом
        foreach($tags as $tag_k => $tag_v){
            if($tags_mc[$tag_k]>$tag_v){
                if($lock->set($key)) {
                    return false;
                }
                return $cobj['data'];        
            }
        }
        
        return $cobj['data'];
    }
    
    /*
     * Установка значения кеша по ключу вместе с тегами и указанием срока годности кеша
     * Проверяется установка блокировки
     * function set
     * @param $CacheVal string, $tags array, $LifeTime int
     */
    function set($CacheVal, $tags, $LifeTime){
        $thetime = time();
        # проверяем наличие тегов и при необходимости устанавливаем их
        $tags_cnt = count($tags);
        
        if( 0==$tags_cnt || false===($tags_mc = self::$memstore->get( $tags )) )
           $tags_mc = Array();
        
        if( $tags_cnt>0 && count($tags_mc)!= $tags_cnt) {
            for($i=0;$i<$tags_cnt;$i++)
               if(!isset($tags_mc[$tags[$i]])) {
                    $tags_mc[$tags[$i]] = $thetime;
                    self::$memstore->set( $tags[$i], $thetime);
                }
        }
        $expire = (((0==$LifeTime)?(self::MAX_LTIME):$LifeTime)+$thetime);
        $cobj = Array(
                      'data' => $CacheVal,
                      'tags' => $tags_mc
                     );
        
        self::$memstore->set(self::EXPR_PREF.$this->key, $expire);
        self::$memstore->set($this->key, $cobj);
        
        return $CacheVal;
    }
    
    /*
     * Полная очистка текущего кеша без поддержки переекеширования. Если нужно удаление с перекешированием, то нужно использовать теги
     * function del
     */
    function del(){
        //return self::$memstore->del($CacheKey);
        //return self::$memstore->set(self::EXPR_PREF.$CacheKey, 0);
        return self::$memstore->del(self::EXPR_PREF . $this->key);
    }
    
    /*
     * tagsType()
     * @param void
     * @return string Cache tag type throw CacheTagTypes namespace
     */
    function tagsType() {
        return CacheTagTypes::MC;
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

