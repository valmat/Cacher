<?php

/**
 * Слотоподобный интерфейс для тегов кеширования.
 * 
 */
 
abstract class Cacher_Tag 
{
    /**
     *  NameSpase prefix for cache key
     *  var set as default mast by redeclaradet
     *  @var string
     */
    const  NAME_SPACE = 'dflt_k';
    
    /**
     *  Backend object responsible for this cache slot.
     *  @var Cacher_Backend
     */
    protected   $Backend;
    
    /**
     *  Backend name
     *  @var string
     */
    public    $BackendName;
    /**
     * Calculated ID associated to this slot.
     * 
     * @var string
     */
    protected $tagval = null;


    /**
     * Creates a new Tag object.
     * @return Cacher_Tag
     */
    public function __construct($tagval){
        $this->tagval = self::NAME_SPACE . $tagval;
    }
    
    /*
     * function getVal
     * @param void
     * @return mixed
     */
    public function getVal() {
        return $this->tagval;
    }
    
    /**
     * Clears all keys associated to this tags.
     * @param void
     * @return void
     */
    public function clear(){
        $this->Backend->clearTag($this->tagval);
    }



}
?>