<?php

/*
 * class Cacher_Backend_MemReCache
 * 
 * ������ ������ Cacher ��� ����������� � memcache c ���������� ����������������.
 * ��� ��������������� ������������ ����������.
 * ����� ������� ����������� ��������� ����� � ����������� ���� ���������� ������ ���� �������.
 * ��� �������� ������ �������� �������� ���������� ���������� ���.
 * ��� � � Cacher_Backend_Memcache ������������ ����.
 * � ����������� ������� ����������� �������� 'expire'. ����� ������� �� ���������� ����� �������� ���� ������ ������� �� memcache,
 * � ��� �����.
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
      * ���� ������������� ����������
      * ����� ��������� ���� ���� ���������� � true
      * � ������ set ����������� ������ ����, � ������ ���� �� ����������, ����� ��������� ���������� [self::$memcache->delete(self::LOCK_PREF . $CacheKey)]
      * ����� ���� ���������� ������ ���� ����: $this->is_locked = false;
      */
    private        $is_locked = false;
    
    function __construct() {
        if(null==self::$memcache){
           $memcache = new Memcache;
           $memcache->connect(self::MC_HOST, self::MC_PORT);
        }
    }
    
    /*
     * ��������� �� ��������� �� ��� ���� ����������
     * ���� ���������� �� �����������, �������� ������� �� ������� add, ��� �� ������������� ��������� �����
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
        # ���� ������� � ���� �� �������, �� ���������� ������������
        if( false===($cobj = self::$memcache->get($CacheKey)) )
           return false;

        # ���� ����� ����� ���� �������, �� ������������ � �������� ����������
        if($cobj['expire'] < time()){
          # �������� ���������� ����������
          # ���� ���������� ���������� ��, �� ������������ ��������������, ����� ���������� ���������� ������ �� ����
          if($this->set_lock())
            return false;
          return $cobj['data'];
        }
        $tags = $cobj['tags'];
        $tags_cnt = count($tags);
        
        # ���� ����� ���, �� ������ ������ ������. ����� ������ ����� ������� 0!=$tags_cnt
        if(0==$tags_cnt)
          return $cobj['data'];

        $tags_mc = self::$memcache->get( array_keys($cobj['tags']) );
        # ���� � ���� ������� ���������� � ����� ���� ����, �� ������������ ��� ��������������� � ���� �����
        if( count($tags_mc)!= $tags_cnt){
          if($this->set_lock())
            return false;
          return $cobj['data'];        
        }
        
        # ���� ��� ������ �� �����, �� �������� �� ����
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
     * ��������� �������� ���� �� ����� ������ � ������ � ��������� ����� �������� ����
     * ����������� ��������� ����������
     * function set
     * @param $CacheVal string,$CacheKey  string, $tags array, $LifeTime int
     */
    function set(string $CacheVal,string $CacheKey, Array $tags, int $LifeTime=null){
        $thetime = time();
        # ��������� ������� ����� � ��� ������������� ������������� ��
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
