<?php

/*
 * class Cacher_Backend_MemReFile
 * 
 * ������ ������ Cacher ��� ����������� � memcache � � �������� ������� c ���������� ����������������.
 * ��� ��������������� ������������ ���������� �� memcache.
 * ����� ������� ����������� ��������� ����� � ����������� ���� ���������� ������ ���� �������.
 * ��� �������� ������ �������� �������� ���������� ���������� ���.
 * ��� � � Cacher_Backend_Memcache ������������ ����.
 * � ����������� ������� ����������� �������� 'expire'. ����� ������� �� ���������� ����� �������� ���� ������ ������� �� memcache,
 * � ��� �����.
 * � ������� �� Cacher_Backend_MemReCache0 � ������ ������ ��� �������� ���� �� ����� ���������� �� ����������� �������� ���� �� ������
 * Memcache, � ���� ����� ��������� Expire. �� ���� ����� �������� ����� ���� ��� ������������� ����� � ������������ ������������ ���������������
 * ������ ���� ���� ����������� � �������� �������.
 * �� ���� ����� �� �������� ������� �� �������� ���� � ������ ��� ������ � ����������� ������ ��� ����������� ������.
 * ����������� � ������� ������� �������� ����� ����������� �������, ��� �� ������������� ������� �� ������.
 * �� ���� ��� �������� �� ����� ������, ���� ������� ��� � ������ (��������� � memcache)
 * ��� ���������� ��������� ���� ������ ���������� (������� ������� ���������) ������������ ��������� ������� rename()
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 *  CacheObj(in Memcache):
 *  'cache_key'=Array(
 *        'data' => <cached data>
 *        'tags' => Array(
 *                       'tag1' => ...,
 *                       'tag2' => ...,
 *                       'tag3' => ...
 *                       )
 *                  );
 *  CacheObj(in File):
 *  <cached data>
 * LockFlag:
 * '~lock'.'cache_key' = true/false
 * Expire:
 * '~xpr'.'cache_key' = ...
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * 
 */

class Cacher_Backend_MemReFile extends Cacher_Backend{
    
    private static $memcache=null;
    
    const NAME      = 'MemReFile';
    /**
      * ������ memcache
      */
    const COMPRES   = false;//MEMCACHE_COMPRESSED;
    /**
      * ������� ��� ������������ ����� ����������
      */
    const LOCK_PREF = CONFIG_Cacher_BK_MemReFile::LOCK_PREF;
    /**
      * ����� ����� ����� ����������. ���� �� ����� ������������ ���� ������� �������� ����������,
      * �� ���������� ��������� ���������� � ������ �������� ����� ���������� �������� ��������� ��� LOCK_TIME ������.
      * � ������ ������� ���� ���� ���������� ������� �� ����, ��� ��� ����� ����������, �� ��������� ��������� ����� � ������������� �������� ���������� ��������.
      * �.�. LOCK_TIME ����� ������������� �����, ��� �� ��� ����� ����� ���� ��������, � �� ������� ������, ��� �� ���������� ���� ���� ������� � ������ �������
      */
    const LOCK_TIME = CONFIG_Cacher_BK_MemReFile::LOCK_TIME;
    /**
      * MAX_LifeTIME - ������������ ����� ����� ����. �� ��������� 29 ����. ���� ������ set ������� $LifeTime=0, �� ����� ����������� 'expire' => (time()+self::MAX_LTIME)
      */
    const MAX_LTIME = CONFIG_Cacher_BK_MemReFile::MAX_LTIME;
    /**
      * EXPIRE PREFIX - ������� ��� �������� ����� �� �������� ��������� ����
      */
    const EXPR_PREF = CONFIG_Cacher_BK_MemReFile::EXPR_PREF;
    /**
      * CACHE PATH - ���� � ���������� �������� ����
      */
    const CACHE_PATH = CONFIG_Cacher_BK_MemReFile::CACHE_PATH;
    /**
      * Cache file path depth - ������� ����������� ������ � �����
      */
    const CF_DEPTH   = CONFIG_Cacher_BK_MemReFile::CF_DEPTH;
    
    /**
      * ���� ������������� ����������
      * ����� ��������� ���� ���� ���������� � true
      * � ������ set ����������� ������ ����, � ������ ���� �� ����������, ����� ��������� ���������� [self::$memcache->delete(self::LOCK_PREF . $CacheKey)]
      * ����� ���� ���������� ������ ���� ����: $this->is_locked = false;
      */
    private $is_locked = false;
    
    /**
      * ������ ���� (������������ self::CACHE_PATH) � ��������� ���� ��� ������� �����
      */
    private $fullpath = '';
    
    /**
      * ������ ����� � ��������� ���� � �������� �������������
      */
    private $patharr = Array();
    
    function __construct($CacheKey, $nameSpace) {
        //parent::__construct($CacheKey, $nameSpace);
        $this->nameSpace = $nameSpace;        
        $this->key = $nameSpace . $CacheKey;
        self::$memcache = Mcache::init();
    }
    
    /*
     * ��������� �� ��������� �� ��� ���� ����������
     * ���� ���������� �� �����������, �������� ������� �� ������� add, ��� �� ������������� ��������� �����
     * function set_lock
     */
    private function set_lock() {
        if( !($this->is_locked) && !(self::$memcache->get(self::LOCK_PREF . $this->key)) )
           $this->is_locked = self::$memcache->add(self::LOCK_PREF . $this->key,true,false,self::LOCK_TIME);
        return $this->is_locked;
    }
    
    function get(){
        # ���� ������� � ��� ���� �� �������, �� ���� � �����
        # � ����� � ������-�� �������� ������ � memcache �������� ����� � ������������� �� ������.
        //if( false===( $c_arr = self::$memcache->get( Array( $this->key, self::EXPR_PREF . $this->key ) )) || !isset($c_arr[$this->key]) || !isset($c_arr[self::EXPR_PREF . $this->key]) ){
        if( false===( $cobj=self::$memcache->get($this->key) ) ){
           # �������� ���������� ����������
           # ���� ���������� ���������� ��, �� ������������ ��������������, ����� ���������� ���������� ������ �� ����
           if($this->set_lock())
             return false;
           # �������� �������� ��� �� �����
           $this->getPath();
           if( file_exists( $this->fullpath ) )
              return unserialize(file_get_contents( $this->fullpath ));
           # ���� ���� ���� ��� �� �����������, ���������� ������������
           return false;
        }
        
        # ���� ����� ����� ���� �������, �� ������������ � �������� ����������
        if( false===( $expire=self::$memcache->get(self::EXPR_PREF . $this->key) ) || $expire < time() ){
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
     * @param $CacheVal string,  string, $tags array, $LifeTime int
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
        
        self::$memcache->set(self::EXPR_PREF . $this->key, $expire, false, 0);
        self::$memcache->set($this->key, $cobj, self::COMPRES, 0);
        
        # ����� ��� � ����
        # ���� ���������� ��������� ������� �������, �� ����� � ����
        if($this->is_locked){
            $this->getPath();
            $thedir = self::CACHE_PATH;
            for($i=0; $i<=self::CF_DEPTH; $i++){
                if(!is_dir($thedir .= '/' . $this->patharr[$i]))
                   mkdir($thedir, 0700);
            }
            $tmp_file = tempnam($thedir, 'fctmp_');
            file_put_contents($tmp_file, serialize($CacheVal) );
            # �������� ���������� ���� � ������� ���� � �������� ���
            rename($tmp_file, $this->fullpath);
        }        
        
        # ������� ����������
        if($this->is_locked){
            $this->is_locked = false;
            self::$memcache->delete(self::LOCK_PREF . $this->key);
        }
        
        return $CacheVal;
    }
    
    /*
     * ������ ������� �������� ���� ��� ��������� ����������������. ���� ����� �������� � ����������������, �� ����� ������������ ����
     * function del
     */
    function del(){
        return self::$memcache->delete(self::EXPR_PREF . $this->key);
    }
    
    /*
     * function getPath
     * @param $arg
     */
    private function getPath() {
        if(''==$this->fullpath){
            $this->patharr[] = $this->nameSpace;
            $sha1 = sha1($this->key);
            
            for($i=0; $i<self::CF_DEPTH; $i++){
                $this->patharr[] = $sha1[2*$i] . $sha1[2*$i+1];
            }
            $this->patharr[] = substr($sha1, 2*self::CF_DEPTH);
            $this->fullpath = self::CACHE_PATH .'/'. implode('/',$this->patharr);
            
        }
    }
    
}

?>