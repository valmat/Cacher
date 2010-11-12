<?php
   
   define('CACHER_SLOT_REQUIRED',TRUE);

/**  Slots collection
  *  ����-������� ����� ��� ������������� ������� �����������.
  *  ��������� �� � ��������� ��������� ������� ��� ����, ��� �� �������� ����������� �������������� ��������� ��� ���������,
  *  � ���������� ��� �� ���� ����������� �� ������ ������ ����.
  *  
  *  ������������: Cacher::_setOption($BackendName, $LifeTime, $key)
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