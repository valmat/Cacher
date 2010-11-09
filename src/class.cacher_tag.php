<?php

/**
 * интерфейс для тегов кеширования.
 * 
 */
 
abstract class Cacher_Tag 
{
   
    /**
     *  NameSpase prefix for cache key
     *  var set as default mast by redeclaradet
     */
    const  NAME_SPACE = CACHER_TAG_NAME_SPACE;
    
    /**
     *  Path to Tags backends
     */
    const  TAG_BACKENDS = CACHER_TAG_BACKENDS;

    /**
     *  Path to Tags definitions 
     */
    const  PATH_TAGS  = CACHER_PATH_TAGS;
    
    /**
     *  Backend object responsible for this cache tag.
     *  @var Cacher_Backend
     */
    protected   $Backend = null;
  
    /**
     * Calculated ID associated to this Tag
     * @var string
     */
    protected $tagkey = null;

    /**
     * Creates a new Tag object.
     * Создатель тегов кеширования. Factory method
     * Цель: вынести операцию создания тега из класса Cacher, так как в этом случае для создания каждого тега создается экземпляр кеширующего бекенда
     * Создает экземпляр потомка класса Cacher_Tag
     * @return Cacher_Tag
     * @param $arg      mixed arg for tag create
     * @param $TagName  string name of tag
     */
    static function create($TagName, $arg){
        if (!defined('CACHER_TAG_REQUIRED'))
          require self::PATH_TAGS;
        
        $TagName = 'Cacher_Tag_'.$TagName;
        return new $TagName($arg);        
    }
    
    /**
     * Creates a new Tag object
     * Templated method
     * @return Cacher_Tag
     * @param $arg mixed
     */
    public function __construct(&$arg){
        $this->tagkey = self::setKey($arg);
        //$this->tagkey = self::NAME_SPACE . $tagkey;
        //$this->getKey();
    }
    
    /**
     * Get tag key without create tag object
     * Usege for add tag key in cacher slot
     * @return string   Cacher Tag Key
     * @param $arg      mixed arg for tag create
     * @param $TagName  string name of tag
     */
    static function tagKey($TagName, $arg){
        if (!defined('CACHER_TAG_REQUIRED'))
          require self::PATH_TAGS;
        
        return call_user_func('Cacher_Tag_'.$TagName.'::setKey', $arg);
    }
    
    /**
     * Clears all caches associated to this tags.
     * @param void
     * @return void
     */
    public function clear(){
        return $this->getBackend()->clearTag($this->tagkey);
    }
    
    /**
     * Clears all caches associated to this tags.
     * @param void
     * @return void
     */
    public function getKey(){
        return $this->tagkey;
    }    

    /**
     * Get Cache tag backend object
     * Сделано для создания объекта бэкенда только по требованию
     * @param void
     * @return void
     */
    private function getBackend(){
        if(null==$this->Backend){
            $BackendName = self::getBkName();
            require_once self::TAG_BACKENDS.'tag_'.strtolower($BackendName).'.php';
            $BackendName = 'Cache_Tag_Backend_'.$BackendName;
            $this->Backend = new $BackendName();
        }
        return $this->Backend;
    }
    
    /*
     * abstract function setKey
     * @param void
     * @return string tag key
     */
    abstract function setKey($var);
    
    /*
     * abstract function getBkName
     * 
     * @param void
     * @return string Tag Backend name
     */
    abstract function getBkName();

}

/*******************************************************************************
 * Интерфейс бэкэнда для тегов кеширования.
 * 
 */

interface Cache_Tag_Backend
 {
     /*
     * Очишает кеш по тегу
     * function clearTag
     * @param $tagKey   string
     */
    function clearTag($tagKey);
 }
?>