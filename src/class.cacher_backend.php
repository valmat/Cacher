<?php

/**
 * Интерфейс бэкэнда для кеширования.
 * 
 */

abstract class Cacher_Backend {
    
    protected $key;
    protected $name;
        
    /*
     * Получить значение кеша если есть, или false, если отсутствует.
     * function get
     */
    public function get() {
        echo (is_array($this->key))?'multiGet()':'singleGet()';
        return (is_array($this->key))?$this->multiGet():$this->singleGet();
    }
    
    /*
     * Получить значение кеша если есть, или false, если отсутствует.
     * Используется, когда передается строковой ключ
     * function get
     */
    abstract protected function singleGet();
        
    /*
     * Получить значение кеша если есть, или false, если отсутствует.
     * Используется, когда передается массив ключей
     * function get
     */
    abstract protected function multiGet();
    
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
        $class_name = get_class($this);
        $this->name = substr($class_name,15, strlen($class_name));
        $this->key  = $CacheKey;
    }
 }


?>