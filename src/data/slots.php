<?php
   
   define('CACHER_SLOT_REQUIRED',TRUE);

/**  Slots collection
  *  Слот-функции нужны для инициализации объекта кеширования.
  *  Вынесение их в отдельную сущьность сделано для того, что бы значения кеширования устанавливлись прозрачно для программы,
  *  а управление ими не было распределно по разным частям кода.
  *  
  *  Используется: Cacher::_setOption($BackendName, $LifeTime, $key)
  */


    /***************************************************************************
     * function cacher_slot_user
     * @param $arg
     */
        
    function Cacher_Slot_User(Cacher $self, User $arg) {
       $self->_setOption(CacheTypes::FAST , 10, "user_{$arg->id}");
    }
        
    function Cacher_Slot_User1(Cacher $self, User1 $arg) {
       $self->_setOption(CacheTypes::FAST , 10, "user_{$arg->id}");
    }    

?>