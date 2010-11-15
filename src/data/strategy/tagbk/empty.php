<?php

/*
 * class Cache_Tag_Backend_empty
 * 
 */

class Cache_Tag_Backend_empty implements Cache_Tag_Backend {
    

    function __construct() {}
    
    function clearTag($tagKey){
        return false;
    }
    
}

?>