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
     * Calculated Key associated to this slot.
     * @var string
     */
    private    $CacheKey = null;
    /**
     * Tags attached to this slot.
     * @var array of Cacher_Tag
     */
    private    $Tags = Array();
    
    /*
     * private constructor
     */
    private function __construct() {
        //$this->Tags =
      }    
    
    /*
     * ��������� ����������� ����� ����������� $SlotName � �������� ��� ����������� ��� �������� ��������� $arg
     * ����������� ����������� � ������� ������� - ������ ������ Cacher
     * Init new Cacher Slot
     * function Slot
     * @param $SlotName
     * @param $arg
     */
    static function create($SlotName, $arg) {
      $SelfObj = new Cacher();
      if (!defined('CACHER_SLOT_REQUIRED'))
        require self::PATH_SLOTS;
        
      $SlotName = 'Cacher_Slot_'.$SlotName;
      $SlotName($SelfObj, $arg);
      return $SelfObj;
    }
    
    /*
     * function _setOption
     * ���� ����� ������ ��� ������������� � � �����
     * 
     * @param $Backend Cacher_Backend
     * @param $LifeTime int
     * @param $key string
     */
    public function _setOption($BackendName, $LifeTime, $key) {
        $this->BackendName = $BackendName;
        $this->Backend = self::setBackend($BackendName);
        $this->LifeTime = $LifeTime;
        $this->CacheKey = self::NAME_SPACE.$key;
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
     */
    static function setBackend($BackendName) {
        /*
        if(!class_exists('Cacher_Backend_'.$BackendName,false)){
            require self::PATH_BACKENDS.strtolower($BackendName).'.php';
          }
        */
        require_once self::PATH_BACKENDS . strtolower($BackendName) . '/slot.php';
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
    public function get() {
        return $this->Backend->get($this->CacheKey);
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
        return $this->Backend->set($this->CacheKey, $val, $this->Tags, $this->LifeTime);
    }

    /*
     * Removes a data of specified slot.
     * ������� ���
     * function del
     * @param void
     * @return void
     */
    public function del() {
        $this->Backend->del($this->CacheKey);
    }
  
}

?>
