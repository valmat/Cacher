<?php

/*
 * class Cacher_Backend_Memcache
 *
 * ������ �����:
 *  tag1 -> 25
 *  tag2 -> 63
 *  ��� �������:
 *  [
 *  ���� ��������: 2008-11-07 21:00
 *  ������ ����: [
 *                 ...
 *               ]
 *  ����: [
 *         tag1: 25
 *         tag2: 63
 *        ]
 *  ]
 **********************************************************************
 *  CacheObj = Array(
 *      'expire' => ...
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

class Cacher_Backend_Memcache extends Cacher_Backend{
    
    private static $memcache=null;
    
    const MC_HOST = 'unix:///tmp/memcached.socket';
    const MC_PORT = 0;
    const NAME = 'Memcache';
    
    function __construct() {
        if(null==self::$memcache){
           $memcache = new Memcache;
           $memcache->connect(self::MC_HOST, self::MC_PORT);
        }
    }
    
    function clearTag(string $tagKey){
        self::$memcache->set($tagKey, time(),false,0 );
    }
    
    function get(string $CacheKey){
        if( !($cobj = self::$memcache->get($CacheKey)) );
           return false;
        # ���� ����� ����� ���� �������
        if($cobj['expire'] < time())
          return false;
        $tags = $cobj['tags'];
        $tags_cnt = count($tags);
        $tags_mc = self::$memcache->get( array_keys($cobj['tags']) );
        # ���� � ���� ������� ���������� � ����� ���� ����, �� ������������ ��� ��������������� � ���� �����
        if( count($tags_mc)!= $tags_cnt)
          return false;
        # ���� ��� ������ �� �����, �� �������� �� ����
        for($i=0;$i<$tags_cnt;$i++){
            if($tags_mc>$tags)
              return false;
        }
        return $cobj['data'];
    }
    function set(string $CacheVal,string $CacheKey, Array $tags, $LifeTime=0){
        $thetime = time();
        # ��������� ������� ����� � ��� ������������� ������������� ��
        $tags_mc = self::$memcache->get( $tags );
        $tags_cnt = count($tags);
        if( count($tags_mc)!= $tags_cnt)
          {
            for($i=0;$i<$tags_cnt;$i++)
               if(!isset($tags_mc[$tags[$i]]))
                  {
                    
                    $tags_mc[$tags[$i]] = $thetime;
                    self::$memcache->set($tags[$i], $thetime,false,0 );
                  }
               
          }
        $cobj = Array(
                      'expire' => ($LifeTime+$thetime),
                      'data' => $CacheVal,
                      'tags' => $tags_mc
                     );
        self::$memcache->set($CacheVal, $cobj,false,0 );
        return $CacheVal;
    }
    
    function del(string $CacheKey){
        return self::$memcache->delete($CacheKey);
    }
    
}

?>
