<?php

/**
 * Интерфейс бэкэнда для кеширования.
 * 
 */

abstract class Cacher_Backend {
    
    protected $key;
        
    /*
     * Получить значение кеша если есть, или false, если отсутствует.
     * Используется, когда Cacher инициализирован строковым ключем
     * function get
     */
    abstract public function get();
        
    /*
     * Получить значение кеша если есть, или false, если отсутствует.
     * Используется, когда передается массив ключей
     * function multiGet
     */
    abstract static function multiGet($keys);
    
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
    abstract function tagsType();
    
    /*
     * __construct()
     * @param $CacheKey string
     */
    function __construct($CacheKey) {
        $this->key  = $CacheKey;
    }
    
 }


?>