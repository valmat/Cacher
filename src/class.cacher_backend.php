<?php

/**
 * Интерфейс бэкэнда для кеширования.
 * 
 */

abstract class Cacher_Backend
 {
    private $key;
    private $nameSpace;
        
    /*
     * Получить значение кеша если есть, или false, если отсутствует.
     * function get
     */
    abstract function get();
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
     * tagType()
     * @param void
     * @return string Cache tag type throw CacheTagTypes namespace
     */
    abstract function tagType(); /*{return CacheTagTypes::FAST;}*/
    
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