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

class Cacher_Backend_MemReCache extends Cacher_Backend {
    
    private static $memcache=null;
    
    const NAME      = 'MemReCache';
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
    
    function get(){
        # Если объекта в кеше не нашлось, то безусловно перекешируем
        # В связи с скаким-то странным глюком в memcache красивая схема с мултизапросом не прошла.
        //if( false===( $c_arr = self::$memcache->get( Array( $this->key, self::EXPR_PREF . $this->key ) )) || !isset($c_arr[$this->key]) || !isset($c_arr[self::EXPR_PREF . $this->key]) ){
        if( false===( $cobj=self::$memcache->get($this->key) ) ){
           return false;
        }

        //$cobj   = $c_arr[$this->key];
        //$expire = $c_arr[self::EXPR_PREF . $this->key];
        
        # Если время жизни кеша истекло, то перекешируем с условием блокировки
        if( false===( $expire=self::$memcache->get(self::EXPR_PREF . $this->key) ) || $expire < time() ){
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
     * @param $CacheVal string, $tags array, $LifeTime int
     */
    function set($CacheVal, $tags, $LifeTime){
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
        $expire = (((0==$LifeTime)?(self::MAX_LTIME):$LifeTime)+$thetime);
        $cobj = Array(
                      'data' => $CacheVal,
                      'tags' => $tags_mc
                     );
        
        self::$memcache->set(self::EXPR_PREF.$this->key, $expire, false, 0);
        self::$memcache->set($this->key, $cobj, self::COMPRES, 0);
        
        if($this->is_locked){
            $this->is_locked = false;
            self::$memcache->delete(self::LOCK_PREF . $this->key);
        }
        
        return $CacheVal;
    }
    
    /*
     * Полная очистка текущего кеша без поддержки переекеширования. Если нужно удаление с перекешированием, то нужно использовать теги
     * function del
     */
    function del(){
        //return self::$memcache->delete($CacheKey);
        //return self::$memcache->set(self::EXPR_PREF.$CacheKey, 0, false, 0);
        return self::$memcache->delete(self::EXPR_PREF . $this->key);
    }
    
    /*
     * tagsType()
     * @param void
     * @return string Cache tag type throw CacheTagTypes namespace
     */
    function tagsType() {
        return CacheTagTypes::FAST;
    }    
    
}

?>