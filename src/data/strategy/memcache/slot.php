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
    
    private static $memcache=null;
    
    const NAME    = 'Memcache';
    const COMPRES = false;//MEMCACHE_COMPRESSED;
    
    
    function __construct() {
        if(null==self::$memcache){
           self::$memcache = Mcache::init();
        }
    }

    /*
     * Получение кеша
     * function get
     * @param $CacheKey string
     */
    function get($CacheKey){
        # если объекта в кеше не нашлось
        if( false===($cobj = self::$memcache->get($CacheKey)) )
           return false;
        
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
     * @param $CacheVal string,$CacheKey  string, $tags array, $LifeTime int
     */
    function set($CacheKey, $CacheVal, $tags, $LifeTime=0){
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
                      'data' => $CacheVal,
                      'tags' => $tags_mc
                     );
        self::$memcache->set($CacheKey, $cobj, self::COMPRES, $LifeTime);
        return $CacheVal;
    }
    
    /*
     * Удаление кеша по собственному ключу
     * function del
     * @param $CacheKey string
     */
    function del($CacheKey){
        return self::$memcache->delete($CacheKey);
    }
    
}

?>