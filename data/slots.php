<?php
   
   define('CACHER_SLOT_REQUIRED',TRUE);

/**
  *  ����-������� ����� ��� ������������� ������� �����������.
  *  ��������� �� � ��������� ��������� ������� ��� ����, ��� �� �������� ����������� �������������� ��������� ��� ���������,
  *  � ���������� ��� �� ���� ����������� �� ������ ������ ����. 
  */


/*
class Cacher_Slot_User extends Cacher_Slot {
    public function __construct(User $user) {
        parent::__construct("profile_{$user->id}", 3600 * 24);
    }
    protected function _getBackend(){
        return new Backend_MC();
    }
}
*/
    /*
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
