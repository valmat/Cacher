<?php

/**
 * Интерфейс бэкэнда для кеширования.
 * 
 */

abstract class Cacher_Backend {
    
    private $key;
    private $nameSpace;
        
    /*
     * Получить значение кеша если есть, или false, если отсутствует.
     * function get
     */
    public function get() {
        echo "<hr><pre>";
        var_export($this->key);
        echo '</pre><hr>';
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
     * Установить данные в кеш
     * function set
     * @param $CacheVal mixed   Данные кеша
     * @param $tags     array   Массив тегов кеширования
     * @param $LifeTime int     Время жизни кеша
     */
    abstract function set($CacheVal, $tags, $LifeTime);
    
    /*
     * Очистить кеш по ключу
     * function del
     */
    abstract function del();
    
    /*
     * Тип используемых тегов. Знание о тегах должно храниться именно в слоте
     * tagsType()
     * @param void
     * @return string Cache tag type throw CacheTagTypes namespace
     */
    abstract function tagsType(); /*{return CacheTagTypes::FAST;}*/
    
    /*
     * __construct()
     * @param void
     */
    function __construct($CacheKey, $nameSpace) {
        $this->key       = $CacheKey;
        $this->nameSpace = $nameSpace;
    }
 }


?>