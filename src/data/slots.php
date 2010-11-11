<?php
   
   define('CACHER_SLOT_REQUIRED',TRUE);

/**  Slots collection
  *  ����-������� ����� ��� ������������� ������� �����������.
  *  ��������� �� � ��������� ��������� ������� ��� ����, ��� �� �������� ����������� �������������� ��������� ��� ���������,
  *  � ���������� ��� �� ���� ����������� �� ������ ������ ����. 
  */


    /***************************************************************************
     * function cacher_slot_user
     * @param $arg
     */
        
    function Cacher_Slot_User(Cacher $self, User $arg) {
       //echo '<hr><pre>';
       //var_export('Cacher::_setOption("Memcache", 30, '."user_{$arg->id}".');');
       //echo '</pre><hr>';
       
       //Cacher::_setOption('Memcache', 20, "user_{$arg->id}");
       $self->_setOption(CacheTypes::FAST , 10, "user_{$arg->id}");
       
       //Cacher::_setOption($Backend,$LifeTime,$key); 
    }
        
    function Cacher_Slot_User1(Cacher $self, User1 $arg) {
       //echo '<hr><pre>';
       //var_export('Cacher::_setOption("Memcache", 30, '."user_{$arg->id}".');');
       //echo '</pre><hr>';
       
       //Cacher::_setOption('Memcache', 20, "user_{$arg->id}");
       $self->_setOption(CacheTypes::SAFE , 10, "user_{$arg->id}");
       
       //Cacher::_setOption($Backend,$LifeTime,$key); 
    }    

?>
