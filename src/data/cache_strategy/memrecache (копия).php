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

class Cacher_Backend_MemReCache extends Cacher_Backend{
    
    private static $memcache=null;
    
    const MC_HOST   = 'unix:///tmp/memcached.socket';
    const MC_PORT   = 0;
    const NAME      = 'MemReCache';
    const COMPRES   = false;//MEMCACHE_COMPRESSED;
    const LOCK_PREF = '~lock';
    const LOCK_TIME = 5;
    
    /**
      * Флаг установленной блокировки
      * После установки этот флаг помечается в true
      * В методе set проверяется данный флаг, и только если он установлен, тогда снимается блокировка [self::$memcache->delete(self::LOCK_PREF . $CacheKey)]
      * Затем флаг блокировки должен быть снят: $this->is_locked = false;
      */
    private        $is_locked = false;
    
    function __construct() {
        if(null==self::$memcache){
           $memcache = new Memcache;
           $memcache->connect(self::MC_HOST, self::MC_PORT);
        }
    }
    
    /*
     * проверяем не установил ли кто либо блокировку
     * Если блокировка не установлена, пытаемся создать ее методом add, что бы предотвратить состояние гонки
     * function set_lock
     * @param $arg void
     */
    private function set_lock() {
        if( !($this->is_locked) && !($this->is_locked = self::$memcache->get(self::LOCK_PREF . $CacheKey)) )
           $this->is_locked = self::$memcache->add(self::LOCK_PREF . $CacheKey,1,false,self::LOCK_TIME);
        return $this->is_locked;
    }
    
    function clearTag(string $tagKey){
        self::$memcache->set($tagKey, time(),false,0 );
    }
    
    function get(string $CacheKey){
        # если объекта в кеше не нашлось, то безусловно перекешируем
        if( false===($cobj = self::$memcache->get($CacheKey)) )
           return false;

        # Если время жизни кеша истекло, то перекешируем с условием блокировки
        if($cobj['expire'] < time()){
          # Пытаемся установить блокировку
          # Если блокировку установили мы, то отправляемся перекешировать, иначе возвращаем устаревший объект из кеша
          if($this->set_lock())
            return false;
          return $cobj['data'];
        }
        $tags = $cobj['tags'];
        $tags_cnt = count($tags);
        
        # Если тегов нет, то просто отдаем объект. Тогда дальше можно считать 0!=$tags_cnt
        if(0==$tags_cnt)
          return $cobj['data'];

        $tags_mc = self::$memcache->get( array_keys($cobj['tags']) );
        # Если в кеше утеряна информация о каком либо теге, то сбрасывается кеш ассоциированный с этим тегом
        if( count($tags_mc)!= $tags_cnt){
          if($this->set_lock())
            return false;
          return $cobj['data'];        
        }
        
        # Если кеш протух по тегам, то сообщаем об этом
        foreach($tags as $tag_k => $tag_v){
            if($tags_mc[$tag_k]>$tag_v){
              if($this->set_lock())
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
     * @param $CacheVal string,$CacheKey  string, $tags array, $LifeTime int
     */
    function set(string $CacheVal,string $CacheKey, Array $tags, int $LifeTime=null){
        $thetime = time();
        # проверяем наличие тегов и при необходимости устанавливаем их
        $tags_cnt = count($tags);
        
        if( 0==$tags_cnt || false===($tags_mc = self::$memcache->get( $tags )) )
           $tags_mc = Array();
        
        if( $tags_cnt>0 && count($tags_mc)!= $tags_cnt)
          {
            for($i=0;$i<$tags_cnt;$i++)
               if(!isset($tags_mc[$tags[$i]]))
                  {
                    $tags_mc[$tags[$i]] = $thetime;
                    self::$memcache->set( $tags[$i], $thetime, false, 0 );
                  }
          }
        $cobj = Array(
                      'expire' => ($LifeTime+$thetime),
                      'data' => $CacheVal,
                      'tags' => $tags_mc
                     );
        self::$memcache->set($CacheKey, $cobj, self::COMPRES, $LifeTime);

        if($this->is_locked){
            $this->is_locked = false;
            self::$memcache->delete(self::LOCK_PREF . $CacheKey);
        }
        
        return $CacheVal;

    }
    
    function del(string $CacheKey){
        return self::$memcache->delete($CacheKey);
    }
    
}

?>
