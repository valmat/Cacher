<?php

/*
 * class Cacher_Backend_MemReCache0
 * 
 * Бэкенд класса Cacher для кеширования в memcache c безопасным перекешированием.
 * Для перекеширования используются блокировки.
 * Таким образом исключается состаяние гонки и обновлением кеша занимается только один процесс.
 * Тем временем другие процессы временно используют устаревший кеш.
 * Как и в Cacher_Backend_Memcache используются теги.
 * К кешируемому объекту добавляется параметр 'expire'. таким образом за истечением срока годности кеша должен следить не memcache,
 * а сам класс.
 * В данном бекенде удаление по ключу происходит безусловно. То есть. после вызова del перекеширование не возможно.
 * 
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 *  CacheObj:
 *  'cache_key'=Array(
 *      'expire' => ...
 *        'data' => ...
 *        'tags' => Array(
 *                       'tag1' => ...,
 *                       'tag2' => ...,
 *                       'tag3' => ...
 *                       )
 *                  );
 * LockFlag:
 * '~lock'.'cache_key' = 1
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * 
 */

require_once CONFIG_Cacher::PATH_BACKENDS . 'locks/lock.memstore.php';
/**
 *  MEMSTORE
 */

class Cacher_Backend_MemReCache0 implements Cacher_Backend{
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
        if( false===($cobj = self::$memstore->get($this->key)) )
            return false;
        return self::mainGet($this->key, $cobj);
    }
    
    /*
     * Получение кеша для мультиключа
     * function get
     */
    static function multiGet($keys){
        !self::$memstore && (self::$memstore = Memstore::init());
        # если объекта в кеше не нашлось, то безусловно перекешируем
        if( false===($Cobjs = self::$memstore->get($keys)) )
            return false;
        
        $rekeys = array_flip($keys);
        $rez = array_fill_keys($rekeys, false);
        
        foreach($Cobjs as $rekey => $cobj) {
            $rez[$rekeys[$rekey]] = self::mainGet($rekey, $cobj);
        }
        return $rez;
    }
        
    /*
     * function mainGet
     * @param $key string
     * @param $cobj array
     */
    private static function mainGet($key, &$cobj) {
        $lock = self::lock();
        # Если время жизни кеша истекло, то перекешируем с условием блокировки
        if($cobj['expire'] < time()){
            # Пытаемся установить блокировку
            # Если блокировку установили мы, то отправляемся перекешировать, иначе возвращаем устаревший объект из кеша
            if($lock->set($key))
                return false;
            return $cobj['data'];
        }
        $tags = $cobj['tags'];
        $tags_cnt = count($tags);
        
        # Если тегов нет, то просто отдаем объект. Тогда дальше можно считать 0!=$tags_cnt
        if(0==$tags_cnt)
            return $cobj['data'];
        
        $tags_mc = self::$memstore->get( array_keys($cobj['tags']) );
        # Если в кеше утеряна информация о каком либо теге, то сбрасывается кеш ассоциированный с этим тегом
        if( count($tags_mc)!= $tags_cnt){
            if($lock->set($key))
                return false;
            return $cobj['data'];        
        }
        
        # Если кеш протух по тегам, то сообщаем об этом
        foreach($tags as $tag_k => $tag_v){
            if($tags_mc[$tag_k]>$tag_v){
                if($lock->set($key))
                    return false;
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
        
        if( $tags_cnt>0 && count($tags_mc)!= $tags_cnt)
          {
            for($i=0;$i<$tags_cnt;$i++)
                if(!isset($tags_mc[$tags[$i]])) {
                    $tags_mc[$tags[$i]] = $thetime;
                    self::$memstore->set( $tags[$i], $thetime);
                }
          }
        $cobj = Array(
                      'expire' => (((0==$LifeTime)?(self::MAX_LTIME):$LifeTime)+$thetime),
                      'data' => $CacheVal,
                      'tags' => $tags_mc
                     );
        self::$memstore->set($this->key, $cobj);
        return $CacheVal;
    }
    
    /*
     * Полная очистка текущего кеша без поддержки переекеширования. Если нужно удаление с перекешированием, то нужно использовать теги
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
        return CacheTagTypes::MC;
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

