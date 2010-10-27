<?php
   
   define('CACHER_TAG_REQUIRED',TRUE);


class Cacher_Tag_SmplTag extends Cacher_Tag {
    public function __construct(User $var) {
        parent::__construct("profile_{$var->id}");
        //$this->BackendName  ='Memcache';
        $this->BackendName  = CACHER_TYPE_SAFE;
        $this->Backend = Cacher::setBackend($this->BackendName);
        
    }
}


class Cacher_Tag_SmplTag1 extends Cacher_Tag {
    public function __construct(User $var) {
        parent::__construct("userid_{$var->id}");
        //$this->BackendName  ='Memcache';
        $this->BackendName  = CACHER_TYPE_SAFE;
        $this->Backend = Cacher::setBackend($this->BackendName);
        
    }
}


?>
