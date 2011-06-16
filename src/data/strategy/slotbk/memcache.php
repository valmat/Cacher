<?php

/*
 * class Cacher_Backend_Memcache
 * ������ ������ Cacher ��� ����������� � memcache
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

class Cacher_Backend_Memcache  extends Cacher_Backend{
    
    private static $memcache=null;
    
    const NAME    = 'Memcache';
    const COMPRES = false;//MEMCACHE_COMPRESSED;
       
    function __construct($CacheKey, $nameSpace) {
        parent::__construct($CacheKey, $nameSpace);
        $this->key = $nameSpace . $CacheKey;
        self::$memcache = Mcache::init();
    }

    /*
     * ��������� ����
     * function get
     */
    function get(){
        # ���� ������� � ���� �� �������
        if( false===($cobj = self::$memcache->get($this->key)) )
           return false;
        
        $tags = $cobj['tags'];
        $tags_cnt = count($tags);
        
        # ���� ����� ���, �� ������ ������ ������. ����� ������ ����� ������� 0!=$tags_cnt
        if(0==$tags_cnt)
          return $cobj['data'];
        
        $tags_mc = self::$memcache->get( array_keys($cobj['tags']) );
        # ���� � ���� ������� ���������� � ����� ���� ����, �� ������������ ��� ������� ���������������� � ���� �����
        if( count($tags_mc)!= $tags_cnt)
          return false;
        
        # ���� ��� ������ �� �����, �� �������� �� ����
        foreach($tags as $tag_k => $tag_v){
            if($tags_mc[$tag_k]>$tag_v)
              return false;
        }
        
        return $cobj['data'];
    }

    /*
     * ��������� �������� ���� �� ����� ������ � ������ � ��������� ����� �������� ����
     * function set
     * @param $CacheVal string
     * @param $tags array
     * @param $LifeTime int
     */
    function set($CacheVal, $tags, $LifeTime){
        $thetime = time();
        # ��������� ������� ����� � ��� ������������� ������������� ��
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
        self::$memcache->set($this->key, $cobj, self::COMPRES, $LifeTime);
        return $CacheVal;
    }
    
    /*
     * �������� ���� �� ������������ �����
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
        return CacheTagTypes::FAST;
    }    
}

?>
