<?php

/**
 * Интерфейс бэкэнда для кеширования.
 * 
 */

interface Cacher_Backend {
    
    /*
     * Получить значение кеша если есть, или false, если отсутствует.
     * Используется, когда Cacher инициализирован строковым ключем
     * function get
     */
    public function get();
        
    /*
     * Получить значение кеша если есть, или false, если отсутствует.
     * Используется, когда передается массив ключей
     * function multiGet
     */
    static function multiGet($keys);
    
    /*
     * function set
     * Установить данные в кеш
     * @param $CacheVal mixed   Данные кеша
     * @param $tags     array   Массив тегов кеширования
     * @param $LifeTime int     Время жизни кеша
     */
    public function set($CacheVal, $tags, $LifeTime);
    
    /*
     * Очистить кеш по ключу
     */
    public function del();
    
    /*
     * Тип используемых тегов. Знание о тегах должно храниться именно в слоте
     * @param void
     * @return string Cache tag type throw CacheTagTypes namespace
     */
    public function tagsType();
    
    /*
     * Возвращает объект блокировки и спользуемой в этом бэкенде.
     * Либо false, если блокировки не используются
     * @param void
     * @return Cacher_Lock object or false
     */
    public function lock();
    
    /*
     * Возвращает ключ
     * @param void
     * @return string
     */
    public function getKey();
    
 }


?>