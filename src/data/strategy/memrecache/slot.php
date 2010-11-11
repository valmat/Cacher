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
 * � ������� �� Cacher_Backend_MemReCache0 � ������ ������ ��� �������� ���� �� ����� ���������� �� ����������� �������� ���� �� ������
 * Memcache, � ���� ����� ��������� Expire. �� ���� ����� �������� ����� ���� ��� ������������� ����� � ������������ ������������ ���������������
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

class Cacher_Backend_MemReCache implements Cacher_Backend {
    
    private static $memcache=null;
    
    const NAME      = 'MemReCache';
    /**
      * ������ memcache
      */
    const COMPRES   = false;//MEMCACHE_COMPRESSED;
    /**
      * ������� ��� ������������ ����� ����������
      */
    const LOCK_PREF = CACHER_BK_MEMRECACHE_LOCK_PREF;
    /**
      * ����� ����� ����� ����������. ���� �� ����� ������������ ���� ������� �������� ����������,
      * �� ���������� ��������� ���������� � ������ �������� ����� ���������� �������� ��������� ��� LOCK_TIME ������.
      * � ������ ������� ���� ���� ���������� ������� �� ����, ��� ��� ����� ����������, �� ��������� ��������� ����� � ������������� �������� ���������� ��������.
      * �.�. LOCK_TIME ����� ������������� �����, ��� �� ��� ����� ����� ���� ��������, � �� ������� ������, ��� �� ���������� ���� ���� ������� � ������ �������
      */
    const LOCK_TIME = CACHER_BK_MEMRECACHE_LOCK_TIME;
    /**
      * MAX_LifeTIME - ������������ ����� ����� ����. �� ��������� 29 ����. ���� ������ set ������� $LifeTime=0, �� ����� ����������� 'expire' => (time()+self::MAX_LTIME)
      */
    const MAX_LTIME = CACHER_BK_MEMRECACHE_MAX_LTIME;
    /**
      * EXPIRE PREFIX - ������� ��� �������� ����� �� �������� ��������� ����
      */
    const EXPR_PREF = CACHER_BK_MEMRECACHE_EXPR_PREF;
    
    /**
      * ���� ������������� ����������
      * ����� ��������� ���� ���� ���������� � true
      * � ������ set ����������� ������ ����, � ������ ���� �� ����������, ����� ��������� ���������� [self::$memcache->delete(self::LOCK_PREF . $CacheKey)]
      * ����� ���� ���������� ������ ���� ����: $this->is_locked = false;
      */
    private        $is_locked = false;
    
    function __construct() {
        if(null==self::$memcache){
           self::$memcache = Mcache::init();
        }
    }
    
    /*
     * ��������� �� ��������� �� ��� ���� ����������
     * ���� ���������� �� �����������, �������� ������� �� ������� add, ��� �� ������������� ��������� �����
     * function set_lock
     * @param $arg void
     */
    private function set_lock($CacheKey) {
        if( !($this->is_locked) && !(self::$memcache->get(self::LOCK_PREF . $CacheKey)) )
           $this->is_locked = self::$memcache->add(self::LOCK_PREF . $CacheKey,true,false,self::LOCK_TIME);
        return $this->is_locked;
    }
    
    function clearTag($tagKey){
        self::$memcache->set($tagKey, time(), false, 0 );
    }
    
    function get($CacheKey){
        # ���� ������� � ���� �� �������, �� ���������� ������������
        # � ����� � ������-�� �������� ������ � memcache �������� ����� � ������������� �� ������.
        //if( false===( $c_arr = self::$memcache->get( Array( $CacheKey, self::EXPR_PREF . $CacheKey ) )) || !isset($c_arr[$CacheKey]) || !isset($c_arr[self::EXPR_PREF . $CacheKey]) ){
        if( false===( $cobj=self::$memcache->get($CacheKey) ) ){
           return false;
        }

        //$cobj   = $c_arr[$CacheKey];
        //$expire = $c_arr[self::EXPR_PREF . $CacheKey];
        
        # ���� ����� ����� ���� �������, �� ������������ � �������� ����������
        if( false===( $expire=self::$memcache->get(self::EXPR_PREF . $CacheKey) ) || $expire < time() ){
          # �������� ���������� ����������
          # ���� ���������� ���������� ��, �� ������������ ��������������, ����� ���������� ���������� ������ �� ����
          if($this->set_lock($CacheKey))
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
          if($this->set_lock($CacheKey))
            return false;
          return $cobj['data'];        
        }
        
        # ���� ��� ������ �� �����, �� �������� �� ����
        foreach($tags as $tag_k => $tag_v){
            if($tags_mc[$tag_k]>$tag_v){
              if($this->set_lock($CacheKey))
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
    function set($CacheKey, $CacheVal, $tags, $LifeTime=self::MAX_LTIME){
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
        $expire = (((0==$LifeTime)?(self::MAX_LTIME):$LifeTime)+$thetime);
        $cobj = Array(
                      'data' => $CacheVal,
                      'tags' => $tags_mc
                     );
        
        self::$memcache->set(self::EXPR_PREF.$CacheKey, $expire, false, 0);
        self::$memcache->set($CacheKey, $cobj, self::COMPRES, 0);
        
        if($this->is_locked){
            $this->is_locked = false;
            self::$memcache->delete(self::LOCK_PREF . $CacheKey);
        }
        
        return $CacheVal;
    }
    
    /*
     * ������ ������� �������� ���� ��� ��������� ����������������. ���� ����� �������� � ����������������, �� ����� ������������ ����
     * function del
     * @param $CacheKey  string
     */
    function del($CacheKey){
        //return self::$memcache->delete($CacheKey);
        //return self::$memcache->set(self::EXPR_PREF.$CacheKey, 0, false, 0);
        return self::$memcache->delete(self::EXPR_PREF.$CacheKey);
    }
    
}

?>