<?php

  /**
    * class Cacher
    * ������� ������� ������� �������������� �� Cacher_Backend - ��������� �������, ����������� ������ ��� ������ Cacher
    * �������������� Cacher ��������� ��������� ������ ��� ������ � ��������.
    * �������� ����� ���� �������� �������, shared memory, memcache, Sqlite � ������ ������� �����������
    * ��� ������ � ������� �������������� ����� � ����. ����� ����������� � ���� ������ ������������� ������� � ������ ������  � ��������� �������� ������
    *
    * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
    * ������ �������������:
    * 
    * define AnyObj // ����� ���� �����, ������ ��� ������ ������. �� ��������� ���� ������� ���� ������� �������� ����� �, �������� ������� ��������� (������ � ����� �����).
    * Cacher::Slot('AniObj',AniObj); // �������������� ���� �����������. ������ �������� ��� ����������� ����� �����, ������ - ��� ������
    *
    * �������� ������  
    * if (false === ($CacheData = Cacher::get())) { // ���� ������ �� ���� �������� �� �������...
    *     $CacheData = GetFromAnyExternal();        // �������� ������ �� �������� ���������
    *     Cacher::addTag(Cacher::newTag('AniTagData',AniTagDataObj)); // ������� � ������� ��������� ����� ��� � ����� ����� ������������ � ���
    *     $tag2 = Cacher::newTag('AniTagData2',AniTagDataObj1)        // ������� ����� ���
    *     Cacher::addTag($tag2);                                      // ��������� ����� ��� � ����� ����� ������������ � ���
    *     Cacher::set($CacheData);                                    // �������� ������
    * }
    * ...
    * ...
    * ���� ����� ����� �������� ����� ������ ���, �� ����� ����� ������� ���:
    * Cacher::newTag('AniTagData2',AniTagDataObj1)->clear()        // ������� ��� ����
    * 
    * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
    */

class Cacher {
    
    const PATH_TAGS     = CACHER_PATH_TAGS;
    const PATH_SLOTS    = CACHER_PATH_SLOTS;
    const PATH_BACKENDS = CACHER_PATH_BACKENDS;
    
    /**
     *  NameSpase prefix for cache key
     *  var set as default should be redefined
     *  @var string
     */
    const  NAME_SPACE   = CACHER_NAME_SPACE;
    
    /**
     *  Backend object responsible for this cache slot.
     *  @var Cacher_Backend
     */
    private static   $Backend;
    /**
     *  Backend name
     *  @var string
     */
    public static    $BackendName;
    /**
     * Lifetime of this slot.
     * @var int
     */
    private static   $LifeTime;
    /**
     * Calculated Key associated to this slot.
     * @var string
     */
    private static   $CacheKey = null;
    /**
     * Tags attached to this slot.
     * @var array of Cacher_Tag
     */
    private static   $Tags;
    
    private function __construct() {}    
    
    /*
     * ��������� ����������� ����� ����������� $SlotName � �������� ��� ����������� ��� �������� ��������� $arg
     * ����������� ����������� � ������� ������� - ������ ������ Cacher
     * Init new Cacher Slot
     * function Slot
     * @param $SlotName
     * @param $arg
     */
    static function Slot($SlotName,$arg) {
      if (!defined('CACHER_SLOT_REQUIRED'))
        require self::PATH_SLOTS;
        
      $SlotName = 'Cacher_Slot_'.$SlotName;
      $SlotName($arg);
      self::$Tags = Array();
    }
    
    /*
     * function _setOption
     * ���� ����� ������ ��� ������������� � ����� ������
     * 
     * @param $Backend Cacher_Backend
     * @param $LifeTime int
     * @param $key string
     */
    static function _setOption($BackendName,$LifeTime,$key) {
        self::$BackendName = $BackendName;
        self::$Backend = self::setBackend($BackendName);
        self::$LifeTime = $LifeTime;
        self::$CacheKey = self::NAME_SPACE.$key;
    }

    /*
     * ������� ����� ��� � ���������� ������ �� ��������� ������
     * function newTag
     * @param $arg
     */
    static function newTag($TagName,$arg) {
      if (!defined('CACHER_TAG_REQUIRED'))
        require self::PATH_TAGS;
      
      $TagName = 'Cacher_Tag_'.$TagName;
      return new $TagName($arg);
    }
    
    /**
     * ��������� ��� � �����
     * 
     * @param Cacher_Tag $tag   Tag object to associate.
     * @return void
     */
    public function addTag(Cacher_Tag $tag)
    {
        if ($tag->BackendName !== self::$BackendName) {
            trigger_error('Backends for tag ' . get_class($tag) . ' and slot ' . get_class($this) . ' must be same', E_USER_WARNING);
        }
        self::$Tags[] = $tag;
    }
    
    /*
     * ��������� ����� �������� �������
     * ���������� ������ ������� �� ��� �����. ������� ��������� �������� �� ������������ ������ ���.
     * ����� ����� ������������ ���� �����, ��� ��������� ������ ������� �� ������.
     * function setBackend
     * @param $BackendName string
     */
    static public function setBackend($BackendName) {
        if(!class_exists('Cacher_Backend_'.$BackendName,false)){
            require self::PATH_BACKENDS.strtolower($BackendName).'.php';
          }
        $BackendName = 'Cacher_Backend_'.$BackendName;
        return new $BackendName();
    }
    
    /*
     * Get a data of this slot. If nothing is found, returns false.
     * �������� ������ �� ����
     * function get
     * @param void
     * @return mixed   Complex data or false if no cache entry is found.
     */
    static function get() {
        return self::$Backend->get(self::$CacheKey);
    }
    
    /*
     * ���������� ���� �������� $val
     * Saves a data for this slot. 
     * 
     * function set
     * @param mixed $val  Data to be saved.
     * @return bool -� ��������� ��������
     */
    static function set($val) {
        //return self::$LastSlot->set($val);
        
        $tags = array();
        $tagCnt = count(self::$Tags);
        for($i=0;$i<$tagCnt;$i++){
            $tags[] = self::$Tags[$i]->getVal();
        }
        return self::$Backend->set(self::$CacheKey, $val, $tags, self::$LifeTime);
        
    }

    /*
     * Removes a data of specified slot.
     * ������� ���
     * function del
     * @param void
     * @return void
     */
    static function del() {
        self::$Backend->del(self::$CacheKey);
    }
  
}

?>
