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
        
   function Cacher_Slot_Test() {
      //return array(CacheTypes::FAST , 10);
      return array('notag_Memcache' , 10);
      
   }
      
      
   function Cacher_Slot_Test1() {
      return array(CacheTypes::SIMPLEST , 10);
   }
      

