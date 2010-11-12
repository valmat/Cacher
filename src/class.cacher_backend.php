<?php

/**
 * Интерфейс бэкэнда для кеширования.
 * 
 */

interface Cacher_Backend
 {
    /*
     * Получить значение кеша если есть, или false, если отсутствует.
     * function get
     */
    function get();
    /*
     * Установить данные в кеш
     * function set
     * @param $CacheVal mixed   Данные кеша
     * @param $tags     array   Массив тегов кеширования
     * @param $LifeTime int     Время жизни кеша
     */
    function set($CacheVal, $tags, $LifeTime=0);
    /*
     * Очистить кеш по ключу
     * function del
     */
    function del();
 }
?>