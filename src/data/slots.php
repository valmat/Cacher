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
       print_time('in Cacher_Slot_User');
       Cacher::_setOption(CacheTypes::FAST , 10, "user_{$arg->id}");
       print_time('Cacher::_setOption');
       
       
       //Cacher::_setOption($Backend,$LifeTime,$key); 
    }
        
    function Cacher_Slot_User1(User1 $arg) {
       //echo '<hr><pre>';
       //var_export('Cacher::_setOption("Memcache", 30, '."user_{$arg->id}".');');
       //echo '</pre><hr>';
       
       //Cacher::_setOption('Memcache', 20, "user_{$arg->id}");
       Cacher::_setOption(CacheTypes::SAFE , 10, "user_{$arg->id}");
       
       //Cacher::_setOption($Backend,$LifeTime,$key); 
    }    

?>
