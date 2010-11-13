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

final class Cacher {
    
    const PATH_SLOTS    = CONFIG_Cacher::PATH_SLOTS;
    const PATH_BACKENDS = CONFIG_Cacher::PATH_BACKENDS;
    
    /**
     *  NameSpase prefix for cache key
     *  var set as default should be redefined
     *  @var string
     */
    const  NAME_SPACE   = CONFIG_Cacher::NAME_SPACE;
    
    /**
     *  Backend object responsible for this cache slot.
     *  @var Cacher_Backend
     */
    private    $Backend;
    /**
     *  Backend name
     *  @var string
     */
    private    $BackendName;
    /**
     * Lifetime of this slot.
     * @var int
     */
    private    $LifeTime;
    /**
     * Tags attached to this slot.
     * @var array of Cacher_Tag
     */
    private    $Tags = Array();
    
    /*
     * private constructor
     */
    private function __construct() {}
    
    /*
     * ��������� ����������� ����� ����������� $SlotName � �������� ��� ����������� ��� �������� ��������� $arg
     * ����������� ����������� � ������� ������� - ������ ������ Cacher
     * Init new Cacher Slot
     * function Slot
     * @param $SlotName
     * @param $arg
     */
    static function create($SlotName, $arg) {
      if (!defined('CACHER_SLOT_REQUIRED'))
        require self::PATH_SLOTS;
        
      $SlotName = 'Cacher_Slot_'.$SlotName;
      $Options = $SlotName($arg);
      
      $SelfObj = new Cacher();
      $SelfObj->BackendName = $Options[0];
      $SelfObj->LifeTime = $Options[1];
      $SelfObj->Backend = self::setBackend($Options[0]/*BackendName*/, $Options[2]/*CacheKey*/);
      
      return $SelfObj;
    }
    
    /*
     * function setOption
     * ���� ����� ������ ��� ������������� � � �����
     * 
     * @param $Backend Cacher_Backend
     * @param $LifeTime int
     * @param $key string
     */
    static function setOption($BackendName, $LifeTime, $key) {
        return Array($BackendName, $LifeTime, $key);
    }
  
    /**
     * ��������� ��� � �����
     * 
     * @param Cacher_Tag $tag   Tag object to associate.
     * @return void
     */
    public function addTag(Cacher_Tag $tag) {
        if ($tag->getBkName() == $this->BackendName) {
            $this->Tags[] = $tag->getKey();
            return true;
        }
        trigger_error('Backends for tag ' . get_class($tag) . ' and slot ' . get_class($this) . ' must be same', E_USER_WARNING);
        return false;
    }
    
    /*
     * ��������� ����� �������� �������
     * ���������� ������ ������� �� ��� �����. ������� ��������� �������� �� ������������ ������ ���.
     * ����� ����� ������������ ���� �����, ��� ��������� ������ ������� �� ������.
     * function setBackend
     * @param $BackendName string
     * @param $CacheKey string
     */
    static function setBackend($BackendName, $CacheKey) {
        require_once self::PATH_BACKENDS . strtolower($BackendName) . '/slot.php';
        $BackendName = 'Cacher_Backend_'.$BackendName;
        return new $BackendName($CacheKey, self::NAME_SPACE);
    }
    
    /*
     * Get a data of this slot. If nothing is found, returns false.
     * �������� ������ �� ����
     * function get
     * @param void
     * @return mixed   Complex data or false if no cache entry is found.
     */
    public function get() {
        return $this->Backend->get();
    }
    
    /*
     * ���������� ���� �������� $val
     * Saves a data for this slot. 
     * 
     * function set
     * @param mixed $val  Data to be saved.
     * @return bool - ���������� ��������
     */
    public function set($val) {
        return $this->Backend->set($val, $this->Tags, $this->LifeTime);
    }

    /*
     * Removes a data of specified slot.
     * ������� ���
     * function del
     * @param void
     * @return void
     */
    public function del() {
        $this->Backend->del();
    }
  
}

?>