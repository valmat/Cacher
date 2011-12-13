<?php

/**
 * Redis database connection class
 * original here: https://github.com/sash/php-redis/blob/master/Redis.php
 * @forked from sash/Redis
 * @license LGPL
 */

class Redis {
    private $_sock;
    
    function __construct($host='localhost', $port = 6379) {
	$this->_sock = fsockopen ( $host, $port);
    }

    private function read() {
	if ($s = fgets ( $this->_sock )) {
	    return $s;
	}
	if ($this->_sock)
	    fclose ( $this->_sock );
	$this->_sock = NULL;
    }
    
    private function cmdResponse() {
	// Read the response
	$s = trim ( $this->read () );
	if(!strlen($s))
	    return;
	switch ($s[0]) {
	    case '-' : // Error message
		break;
	    case '+' : // Single line response
		return substr ( $s, 1 );
	    case ':' : //Integer number
		return substr ( $s, 1 ) + 0;
	    case '$' : //Bulk data response
		$i = ( int ) (substr ( $s, 1 ));
		if ($i == - 1)
		    return null;
		$buffer = '';
		if ($i == 0){
		    $s = $this->read ();
		}
		while ( $i > 0 ) {
		    $s = $this->read ();
		    $l = strlen ( $s );
		    $i -= $l;
		    if ($i < 0)
			    $s = substr ( $s, 0, $i );
		    $buffer .= $s;
		}
		return $buffer;
		break;
	    case '*' : // Multi-bulk data (a list of values)
		$i = ( int ) (substr ( $s, 1 ));
		if ($i == - 1)
		    return null;
		$res = array();
		for($c = 0; $c < $i; $c ++) {
		    $res[] = $this->cmdResponse ();
		}
		return $res;
		break;
	    default :
		return false;
		break;
	}
    }
    
    private function cmd($command) {
	//echo "<hr><pre style='color:blue'>";var_export($command);echo '</pre><hr>';
	if (is_array($command)){
	    # Use unified command format
	    $s = '*'.count($command)."\r\n";
	    foreach ($command as $m){
		$s.='$'.strlen($m)."\r\n";
		$s.=$m."\r\n";
	    }
	} else {
	    $s = $command . "\r\n";
	}
	while ( $s ) {
	    $i = fwrite ( $this->_sock, $s );
	    if ($i == 0)
		break;
	    $s = substr ( $s, $i );
	}
	return $this->cmdResponse ();
    }
    
    function __destruct() {
	$this->cmd ( 'QUIT' );
	if ($this->_sock)
	    fclose ( $this->_sock );
	$this->_sock = NULL;
    }
    
    /**
     * add if not exist
     * 
     * @param string $key
     * @param mixed $value - string or integer
     * @param int ttl
     * @return bool rezult
     */
    function add($key, $value, $ttl = 0) {
	if( ( $rez = $this->cmd(array('SETNX', $key, $value)) ) && $ttl) {
	    $this->cmd ( array( 'EXPIRE', $key, $ttl) );
	}
	return (bool)$rez;
    }
    
    /**
     * set data
     * 
     * @param string $key
     * @param mixed $value - string or integer
     * @param int ttl
     * @return bool rezult
     */
    function set($key, $value, $ttl = 0) {
	return  'OK' ==
		($ttl ?
			$this->cmd ( array( 'SETEX', $key, $ttl, $value) ):
			$this->cmd ( array( 'SET', $key, $value) ));
    }
    
    /**
     * USAGES:
     *  $this->get('key1')
     *  $this->get(array('key1','key2'))
     * 
     * @param mixed $key - string or array
     * @return mixed Bulk reply | Multi bulk reply
     */
    function get($key) {
	if (is_array($key)) {
	    $keys = $key;
	    array_unshift($key, 'MGET');
	    return array_filter ( array_combine($keys, $this->cmd( $key )),
		    function($v){
			return (NULL!==$v);
		    });
	}
	$rez = $this->cmd ( array("GET", $key));
	return (NULL==$rez)? false : $rez;
    }
    
    /**
     * increment the integer value of key
     * @param $key
     * @param $amount
     * @return int this commands will reply with the new value of key after the increment or decrement. 
     */
    function incr($key, $step = 1) {
	$rez = (1 == $step) ? 
	    $this->cmd( array("INCR", $key) ) :
	    $this->cmd( array("INCRBY",$key, $step) );
	    
	return (NULL===$rez) ? false : $rez;
    }
    
    /**
     * decrement the integer value of key
     * @param $key
     * @param $amount
     * @return int this commands will reply with the new value of key after the increment or decrement. 
     */
    function decr($key, $step = 1) {
	$rez = (1 == $step) ? 
	    $this->cmd( array("DECR", $key) ) :
	    $this->cmd( array("DECRBY",$key, $step) );
	return (NULL===$rez) ? false : $rez;
    }
    
    /**
     * delete by key
     * @param $key
     * @return bool rezult
     */
    function del($key) {
	return (bool) $this->cmd( array("DEL", $key) );
    }
    
    /**
     * Call any non-implemented function of redis using the new unified request protocol
     * @param string $name
     * @param array $params
     */
    function __call($name, $params){
	array_unshift($params, strtoupper($name));
	return $this->cmd($params);
    }

}
