<?php

/**
 * Слотоподобный интерфейс для тегов кеширования.
 * 
 * You may create a cache slot and add a bunch of tugs to it.
 * Tags are typized; each tag is parametrized according to 
 * specific needs.
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
     *
     * @return Cacher_Tag
     */
    public function __construct($tagval)
    {
        $this->tagval = self::NAME_SPACE . $tagval;
    }
    
    /*
     * function getVal
     * @param $arg
     */
    
    public function getVal() {
        return $this->tagval;
    }
    
    /**
     * Clears all keys associated to this tags.
     * 
     * @return void
     */
    public function clear()
    {
        //echo '$this->tagval'.$this->tagval;
        $this->Backend->clearTag($this->tagval);
    }



}
?>