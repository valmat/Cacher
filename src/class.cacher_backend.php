<?php

/**
 * Интерфейс бэкэнда для кеширования.
 * 
 */

abstract class Cacher_Backend {
    
    protected $key;
    protected $multimode = false;
    protected $multirez;
        
    /*
     * Получить значение кеша если есть, или false, если отсутствует.
     * function get
     */
    public function get() {
        echo ($this->multimode)?'multiGet()':'singleGet()';
        
        //$this->multimode && ($this->multirez = array_map('self::set_false', array_flip($this->key)));
        if($this->multimode) {
            $this->multirez = array_fill_keys($this->key, false);
            
            //$this->key = array_combine($this->key, $this->key);
        }
        return ($this->multimode)?$this->multiGet():$this->singleGet();
    }
    
    /*
     * Получить значение кеша если есть, или false, если отсутствует.
     * Используется, когда передается строковой ключ
     * function singleGet
     */
    abstract protected function singleGet();
        
    /*
     * Получить значение кеша если есть, или false, если отсутствует.
     * Используется, когда передается массив ключей
     * function multiGet
     */
    abstract protected function multiGet();
    
    
    public function toFill() {
        if(!$this->multimode) {
            return false;
        }
        $ret = array_keys(array_filter($this->multirez, function($v) {
            return false===$v;
        }));
        return count($ret)?$ret:false;
    }
    
    /*
     * function set
     * Установить данные в кеш
     * @param $CacheVal mixed   Данные кеша
     * @param $tags     array   Массив тегов кеширования
     * @param $LifeTime int     Время жизни кеша
     */
    abstract function set($CacheVal, $tags, $LifeTime);
    
    /*
     * function del
     * Очистить кеш по ключу
     */
    abstract function del();
    
    /*
     * tagsType()
     * Тип используемых тегов. Знание о тегах должно храниться именно в слоте
     * @param void
     * @return string Cache tag type throw CacheTagTypes namespace
     */
    abstract function tagsType(); /*{return CacheTagTypes::FAST;}*/
    
    /*
     * __construct()
     * @param $CacheKey string
     */
    function __construct($CacheKey) {
        $this->key  = $CacheKey;
        $this->multimode = is_array($CacheKey);
    }
    
 }


?>