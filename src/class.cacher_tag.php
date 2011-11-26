<?php

/**
 * интерфейс для тегов кеширования.
 * Создание тега:
 * Cacher_Tag::create($tagname,$tagObjDefiner);
 * 
 */
 
abstract class Cacher_Tag {
   
    /**
     *  NameSpase prefix for cache key
     *  var set as default mast by redeclaradet
     */
    const  NAME_SPACE    = CONFIG_Cacher::TAG_NM_SPACE;
    
    /**
     *  Path to Tags backends
     */
    const  PATH_BACKENDS = CONFIG_Cacher::PATH_BACKENDS;

    /**
     *  Path to Tags definitions 
     */
    const  PATH_TAGS     = CONFIG_Cacher::PATH_TAGS;
    
    /**
     *  Backend object responsible for this cache tag.
     *  @var Cacher_Backend
     */
    protected   $Backend = null;
  
    /**
     * Calculated ID associated to this Tag
     * @var string
     */
    protected   $tagkey  = null;

    /**
     * Creates a new Tag object.
     * Создатель тегов кеширования. Factory method, Templated method
     * Цель: вынести операцию создания тега из класса Cacher,
     * так как в этом случае для создания каждого тега создается экземпляр кеширующего бекенда
     * Создает экземпляр потомка класса Cacher_Tag
     * @return Cacher_Tag
     * @param $arg      mixed arg for tag create
     * @param $TagName  string name of tag
     */
    final static function create($TagName, $arg){
        if (!defined('CACHER_TAG_REQUIRED'))
          require self::PATH_TAGS;
        
        $ClassName = 'Cacher_Tag_'.$TagName;
        $newTag = new $ClassName();
        
        //$newTag->tagkey = self::NAME_SPACE . call_user_func($TagName.'::setKey', $arg);
        $newTag->tagkey = self::NAME_SPACE .':'. $TagName .':'. $arg;
        return $newTag;
    }
    
    /**
     * private constructor
     * for create object use Cacher_Tag::create()
     */
    private function __construct(){}
    
    /**
     * Clears all caches associated to this tags.
     * @param void
     * @return void
     */
    final public function clear(){
        return $this->getBackend()->clearTag($this->tagkey);
    }
    
    /**
     * Clears all caches associated to this tags.
     * @param void
     * @return void
     */
    final public function getKey(){
        return $this->tagkey;
    }    

    /**
     * Get Cache tag backend object
     * Сделано для создания объекта бэкенда только по требованию
     * @param void
     * @return void
     */
    private function getBackend(){
        if(null==$this->Backend) {
            $BackendName = $this->getBkName();
            require_once self::PATH_BACKENDS .'tagbk/'. strtolower($BackendName) . 'tag.php';
            $BackendName = 'Cache_Tag_Backend_'.$BackendName;
            $this->Backend = new $BackendName();
        }
        return $this->Backend;
    }
    
    /*
     * abstract function getBkName
     * @param void
     * @return string Tag Backend name
     */
    abstract static function getBkName();

}

/*******************************************************************************
 * Интерфейс бэкэнда для тегов кеширования.
 * 
 */

interface Cache_Tag_Backend {
     /*
     * Очишает кеш по тегу
     * function clearTag
     * @param $tagKey   string
     */
    function clearTag($tagKey);
 }
