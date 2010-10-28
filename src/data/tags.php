<?php
   
   define('CACHER_TAG_REQUIRED',TRUE);


/**  Tags collection
  *  “еги кешировани€
  *  ¬ынесение их в отдельную сущьность сделано дл€ того, что бы значени€ кешировани€ устанавливлись прозрачно дл€ программы,
  *  а управление ими не было распределно по разным част€м кода. 
  */

/*
 *   Any simple tag
 * # CACHER_TYPE_SAFE
 */
class Cacher_Tag_SmplTag extends Cacher_Tag {
    public function __construct(User $var) {
        parent::__construct("profile_{$var->id}");
        $this->BackendName  = CACHER_TYPE_SAFE;
        $this->Backend = Cacher::setBackend($this->BackendName);
        
    }
}

/*
 *   Any simple tag 1
 * # CACHER_TYPE_SAFE
 */
class Cacher_Tag_SmplTag1 extends Cacher_Tag {
    public function __construct(User $var) {
        parent::__construct("userid_{$var->id}");
        $this->BackendName  = CACHER_TYPE_SAFE;
        $this->Backend = Cacher::setBackend($this->BackendName);
        
    }
}


?>
