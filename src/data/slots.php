<?php
   
   define('CACHER_SLOT_REQUIRED',TRUE);

/**  Slots collection
  *  Слот-функции нужны для инициализации объекта кеширования.
  *  Вынесение их в отдельную сущьность сделано для того, что бы значения кеширования устанавливлись прозрачно для программы,
  *  а управление ими не было распределно по разным частям кода.
  *  
  *  Используется: Cacher::setOption($BackendName, $LifeTime, $key)
  *  where $BackendTagName in {'Memcache', 'empty'}
  */


    /***************************************************************************
     * function cacher_slot_user
     * @param $arg
     */
        
    function Cacher_Slot_User(User $arg) {
       return Cacher::setOption(CacheTypes::FAST , 10, "user_{$arg->id}");
    }
        
    function Cacher_Slot_User1(User1 $arg) {
       return Cacher::setOption(CacheTypes::FAST , 10, "user_{$arg->id}");
    }    

?>