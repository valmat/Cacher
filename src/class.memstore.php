<?php

  /**
    * class Memstore
    * Класс для инкапсуляции обращений к хранилищам в памяти.
    * Так же позволет осуществлять горячую замену хранилищь
    * 
    * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
    */

final class Memstore {
    
    /**
     *  Store type/ Name of usege memstore:
     *  Mcache, APcache, RedisCache or other implement Memstore_Interface
     *  @var string
     */
    const  STORE   = 'RedisCache';
    
    /**
     *  Backend object responsible for this cache slot.
     *  @var Memstore_Interface
     */
    private static $storeObj = NULL;
    
    static function init(){
        if(NULL===self::$storeObj){
           $storeName = self::STORE;
	   //self::$storeObj = $storeName::Init();
	   self::$storeObj = new $storeName;
        }
        return self::$storeObj;
    }
    private function __construct() {}
    private function __clone() {}
  
}


/**
  *  Memstore_Interface
  */

interface Memstore_Interface {
    /**
     *  Method to initialize the object 
     */
    //static function init();
    
    /*
     * @param $key string or array
     * @return mixed
     */
    public function get($key);
    
    /*
     * Set data at memstore
     * @param $key string  cache key
     * @param $data mixed  cachin data
     * @param $ttl int	   cache time to live in sec. If 0, ot limited
     * @return mixed
     */
    public function set($key, $data, $ttl = 0);
    
    /*
     * Concurrency set data at memstore.
     * If cache with the same key already exists, returns false
     * @param $key string  cache key
     * @param $data mixed  cachin data
     * @param $ttl int	   cache time to live in sec. If 0, ot limited
     * @return mixed
     */
    public function add($key, $data, $ttl = 0);
    
    /*
     * @param $key string
     * @return bool
     */
    public function del($key);
    
    
}


/*******************************************************************************
  *  class Mcache
  *  create on Memcache object for use in difference project construction
  *  for prevention multi memcache connect
  */

class Mcache implements Memstore_Interface {
    
    const  HOST = 'unix:///tmp/memcached.socket';
    const  PORT = 0;
    
    /**
      * сжатие memcache
      */
    const COMPRES   = false;
    
    private static $memcache = NULL;
    
    /*
    static function init(){
        /-*
	if(NULL===self::$memcache){
           self::$memcache = new Memcache;
           self::$memcache->connect(self::HOST, self::PORT);
        }
        return self::$memcache;
	*-/
	echo "<hr><pre>";var_export(debug_backtrace());echo '</pre><hr>';
    }
    */
    
    public function __construct() {
	self::$memcache = new Memcache;
	self::$memcache->connect(self::HOST, self::PORT);
    }
    
    private function __clone() {}
    
    /*
     * @param $key string or array
     * @return mixed
     */
    public function get($key) {
	return self::$memcache->get($key);
    }
    
    /*
     * Set data at memstore
     * @param $key string  cache key
     * @param $data mixed  cachin data
     * @param $ttl int	   cache time to live in sec. If 0, ot limited
     * @return bool
     */
    public function set($key, $data, $ttl = 0) {
	return self::$memcache->set($key, $data, self::COMPRES, $ttl);
    }
    
    /*
     * Concurrency set data at memstore.
     * If cache with the same key already exists, returns false
     * @param $key string  cache key
     * @param $data mixed  cachin data
     * @param $ttl int	   cache time to live in sec. If 0, ot limited
     * @return bool
     */
    public function add($key, $data, $ttl = 0) {
	return self::$memcache->add($key, $data, self::COMPRES, $ttl);
    }
    
    /*
     * @param $key string
     * @return bool
     */
    public function del($key) {
	return self::$memcache->delete($key, 0);
    }
    
}

/*******************************************************************************
  *  class Mcache
  *  create on Memcache object for use in difference project construction
  *  for prevention multi memcache connect
  */

class APcache implements Memstore_Interface {
    
    //public function __construct() {}
    
    private function __clone() {}
    
    /*
     * @param $key string or array
     * @return mixed
     */
    public function get($key) {
	if(!is_array($key)) {
	    return apc_fetch($key);
	}
	$rez = apc_fetch($key);
	return $rez?$rez:array();
    }
    
    /*
     * Set data at memstore
     * @param $key string  cache key
     * @param $data mixed  cachin data
     * @param $ttl int	   cache time to live in sec. If 0, ot limited
     * @return bool
     */
    public function set($key, $data, $ttl = 0) {
	return apc_store($key , $data, $ttl);
    }
    
    /*
     * Concurrency set data at memstore.
     * If cache with the same key already exists, returns false
     * @param $key string  cache key
     * @param $data mixed  cachin data
     * @param $ttl int	   cache time to live in sec. If 0, ot limited
     * @return bool
     */
    public function add($key, $data, $ttl = 0) {
	return apc_add($key , $data, $ttl);
    }
    
    /*
     * @param $key string
     * @return bool
     */
    public function del($key) {
	return apc_delete($key);
    }
    
}


/*******************************************************************************
  *  class Mcache
  *  create on Memcache object for use in difference project construction
  *  for prevention multi memcache connect
  */

class RedisCache implements Memstore_Interface {
    
    const HOST = 'unix:///tmp/redis.sock';
    const PORT = 0;
    private static $r = NULL;
    
    private function __clone() {}
    
    public function __construct() {
	self::$r = new Redis(self::HOST, self::PORT);
    }
    
    /*
     * @param $key string or array
     * @return mixed
     */
    public function get($key) {
	if(!is_array($key)) {
	    return unserialize(self::$r->get($key));
	}
	return 
	    array_combine ($key,
		array_merge (
		    array_fill_keys($key, false),
		    array_map('unserialize',self::$r->get($key))
		)
	    );
    }
    
    /*
     * Set data at memstore
     * @param $key string  cache key
     * @param $data mixed  cachin data
     * @param $ttl int	   cache time to live in sec. If 0, ot limited
     * @return bool
     */
    public function set($key, $data, $ttl = 0) {
	return self::$r->set($key, serialize($data), $ttl);
    }
    
    /*
     * Concurrency set data at memstore.
     * If cache with the same key already exists, returns false
     * @param $key string  cache key
     * @param $data mixed  cachin data
     * @param $ttl int	   cache time to live in sec. If 0, ot limited
     * @return bool
     */
    public function add($key, $data, $ttl = 0) {
	return self::$r->add($key, serialize($data), $ttl);
    }
    
    /*
     * @param $key string
     * @return bool
     */
    public function del($key) {
	return self::$r->del($key);
    }
    
}
