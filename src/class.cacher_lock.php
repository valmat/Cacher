<?php

/**
 * Интерфейс блокировки для бэкэнда для кеширования.
 * 
 */

interface Cacher_Lock {
    
    /*
     * function set
     * проверяем не установил ли кто либо блокировку
     * Если блокировка не установлена, пытаемся создать ее методом add, что бы предотвратить состояние гонки
     * @param $key string
     * @return bool
     */
    static function set($key);
    
    /*
     * function del
     * Удаление блокировки
     * @param $key string
     * @return bool
     */
    static function del($key);
    
    /*
     * function get
     * Проверка установленности блокировки
     * @param $key string
     * @return bool
     */
    static function get($key);
    
}

