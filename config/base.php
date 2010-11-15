<?php
################################################################################

/**
  *  Memcache configs
  *  class Mcache
  *  create on Memcache object for use in difference project construction
  *  for prevention multi memcache connect
  */

 class Mcache {
    
    const  HOST = 'unix:///tmp/memcached.socket';
    const  PORT = 0;
    
    static $memcache = null;
    static function init(){
        if(null===self::$memcache){
           self::$memcache = new Memcache;
           self::$memcache->connect(self::HOST, self::PORT);
        }
        return self::$memcache;
        
    }
    protected function __construct() {}
    protected function __clone() {}
 }

?>