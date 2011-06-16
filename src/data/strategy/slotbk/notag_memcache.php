<?php

/*
 * class Cacher_Backend_notag_Memcache
 * ������ ������ Cacher ��� ����������� � memcache
 *
 * � ���� ������� ���� �� ��������������. �� ��� ������� ������� ��� memcache
 * 
 */

class Cacher_Backend_notag_Memcache  extends Cacher_Backend{
    
    private static $memcache=null;
    
    const NAME    = 'notag_Memcache';
    const COMPRES = false;//MEMCACHE_COMPRESSED;
       
    function __construct($CacheKey, $nameSpace) {
        parent::__construct($CacheKey, $nameSpace);
        $this->key = $nameSpace .'nt'. $CacheKey;
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
        
        return $cobj;
    }

    /*
     * ��������� �������� ���� �� ����� ������ � ������ � ��������� ����� �������� ����
     * function set
     * @param $CacheVal string
     * @param $tags array
     * @param $LifeTime int
     */
    function set($CacheVal, $tags, $LifeTime){
        self::$memcache->set($this->key, $CacheVal, self::COMPRES, $LifeTime);
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
        return CacheTagTypes::NOTAG;
    }    
}

?>
