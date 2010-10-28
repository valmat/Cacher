<?php

/**
 * Интерфейс бэкэнда для кеширования.
 * 
 */

interface Cacher_Backend
 {
     /*
     * Очишает кеш по тегу
     * function clearTag
     * @param $tagKey   string
     */
    function clearTag($tagKey);
    /*
     * Получить значение кеша если есть, или false, если отсутствует.
     * function get
     * @param $CacheKey string  Ключ кеша
     */
    function get($CacheKey);
    /*
     * Установить данные в кеш
     * function set
     * @param $CacheKey string  Ключ кеша
     * @param $CacheVal mixed   Данные кеша
     * @param $tags     array   Массив тегов кеширования
     * @param $LifeTime int     Время жизни кеша
     */
    function set($CacheKey, $CacheVal, $tags, $LifeTime=0);
    /*
     * Очистить кеш по ключу
     * function del
     * @param $CacheKey string
     */
    function del($CacheKey);
 }
?>