<?php
   
   define('CACHER_SLOT_REQUIRED',TRUE);

/**  Slots collection
  *  Слот-функции нужны для инициализации объекта кеширования.
  *  Вынесение их в отдельную сущьность сделано для того, что бы значения кеширования устанавливлись прозрачно для программы,
  *  а управление ими не было распределно по разным частям кода. 
  */


    /***************************************************************************
     * function cacher_slot_user
     * @param $arg
     */
        
    function Cacher_Slot_User(User $arg) {
       //echo '<hr><pre>';
       //var_export('Cacher::_setOption("Memcache", 30, '."user_{$arg->id}".');');
       //echo '</pre><hr>';
       
       //Cacher::_setOption('Memcache', 20, "user_{$arg->id}");
       Cacher::_setOption(CACHER_TYPE_SAFE , 10, "user_{$arg->id}");
       
       //Cacher::_setOption($Backend,$LifeTime,$key); 
    }

?>
