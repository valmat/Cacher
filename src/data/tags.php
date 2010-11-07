<?php
   
   define('CACHER_TAG_REQUIRED',TRUE);


/**  Tags collection
  *  ���� �����������
  *  ��������� �� � ��������� ��������� ������� ��� ����, ��� �� �������� ����������� �������������� ��������� ��� ���������,
  *  � ���������� ��� �� ���� ����������� �� ������ ������ ����. 
  */

/*
 *   Any simple tag
 * # CacheTypes::SAFE
 */
class Cacher_Tag_SmplTag extends Cacher_Tag {
    public function __construct(User $var) {
        parent::__construct('profile_'.$var->id);
        $this->BackendName  = CacheTypes::FAST;
        $this->Backend = Cacher::setBackend($this->BackendName);
        
    }
}

/*
 *   Any simple tag 1
 * # CacheTypes::SAFE
 */
class Cacher_Tag_SmplTag1 extends Cacher_Tag {
    public function __construct(User $var) {
        parent::__construct('userid_'.$var->id);
        $this->BackendName  = CacheTypes::FAST;
        $this->Backend = Cacher::setBackend($this->BackendName);
        
    }
}


?>
