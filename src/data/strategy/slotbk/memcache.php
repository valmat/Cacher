<?php

/*
 * class Cacher_Backend_Memcache
 * Бэкенд класса Cacher для кеширования в memcache
 *
 * Версии тэгов:
 *  tag1 -> 25
 *  tag2 -> 63
 *  Кэш выборки:
 *  [
 *  срок годности: 2008-11-07 21:00
 *  данные кэша: [
 *                 ...
 *               ]
 *  тэги: [
 *         tag1: 25
 *         tag2: 63
 *        ]
 *  ]
 **********************************************************************
 *  CacheObj = Array(
 *      'data' => ...
 *      'tags' => Array(
 *                      'tag1' => ...,
 *                      'tag2' => ...,
 *                      'tag3' => ...
 *                     )
 *      );
 *********************************************************************
 *  
 * 
 */

class Cacher_Backend_Memcache  implements Cacher_Backend{
    
    private static $memcache=NULL;
    private $key;
       
    function __construct($CacheKey) {
        $this->key  = $CacheKey;
        self::$memcache = Mcache::init();
    }

    /*
     * Получение кеша
     * function get
     */
    public function get() {
        # если объекта в кеше не нашлось
        if( false===($cobj = self::$memcache->get($this->key)) )
            return false;
        return self::mainGet($this->key, $cobj);
    }
    
    /*
     * Получение кеша для мультиключа
     * function get
     */
    static function multiGet($keys){
        !self::$memcache && (self::$memcache = Mcache::init());
        # Если объекта в кеше не нашлось, то безусловно перекешируем
        if( false===( $Cobjs = self::$memcache->get( $keys )) ){
            return false;
        }
        
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
        $tags = $cobj['tags'];
        $tags_cnt = count($tags);
        
        # Если тегов нет, то просто отдаем объект. Тогда дальше можно считать 0!=$tags_cnt
        if(0==$tags_cnt)
            return $cobj['data'];
        
        $tags_mc = self::$memcache->get( array_keys($cobj['tags']) );
        # Если в кеше утеряна информация о каком либо теге, то сбрасывается кеш объекта ассоциированного с этим тегом
        if( count($tags_mc)!= $tags_cnt)
            return false;
        
        # Если кеш протух по тегам, то сообщаем об этом
        foreach($tags as $tag_k => $tag_v){
            if($tags_mc[$tag_k]>$tag_v)
                return false;
        }
        
        return $cobj['data'];
    }
        
    /*
     * Установка значения кеша по ключу вместе с тегами и указанием срока годности кеша
     * function set
     * @param $CacheVal string
     * @param $tags array
     * @param $LifeTime int
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
               if(!isset($tags_mc[$tags[$i]])){
                   $tags_mc[$tags[$i]] = $thetime;
                   self::$memcache->set( $tags[$i], $thetime, false, 0 );
               }
          }
        $cobj = Array(
                      'data' => $CacheVal,
                      'tags' => $tags_mc
                     );
        self::$memcache->set($this->key, $cobj, Mcache::COMPRES, $LifeTime);
        return $CacheVal;
    }
    
    /*
     * Удаление кеша по собственному ключу
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
        return CacheTagTypes::MC;
    }
    
    /*
     * Возвращает объект блокировки и спользуемой в этом бэкенде.
     * Либо false, если блокировки не используются
     * @param void
     * @return false
     */
    public function lock() {
        return false;
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

