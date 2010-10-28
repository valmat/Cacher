<?php
################################################################################
/**
  *  [Cacher_Backend]
  *  config for class  extendeds from Cacher_Backend
  */


    /**
      *   Cacher_Backend_MemReFile
      */
    # CACHE PATH - Путь к дериктории хранения кеша. В конце обратный слеш '/'
    define('CACHER_BK_MEMREFILE_C_PATH',   $RootPath . '/sys/var/safe_cache/');
    # TMP PATH - Путь к папке со временными файлами
    define('CACHER_BK_MEMREFILE_TMP_PATH', '/tmp');
    # CACHE EXTENTION - Расширение для файлов кеша
    define('CACHER_BK_MEMREFILE_C_EXT',     '.cache');


?>
