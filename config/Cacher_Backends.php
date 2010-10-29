<?php
################################################################################
/**
  *  [Cacher_Backend]
  *  config for class  extendeds from Cacher_Backend
  */


    /***************************************************************************
      *   Cacher_Backend_MemReFile
      */
    
    
    /**
      * ������� ��� ������������ ����� ����������
      */
    define('CACHER_BK_MEMREFILE_LOCK_PREF', '~lock');
    
    /**
      * ����� ����� ����� ����������. ���� �� ����� ������������ ���� ������� �������� ����������,
      * �� ���������� ��������� ���������� � ������ �������� ����� ���������� �������� ��������� ��� LOCK_TIME ������.
      * � ������ ������� ���� ���� ���������� ������� �� ����, ��� ��� ����� ����������, �� ��������� ��������� ����� � ������������� �������� ���������� ��������.
      * �.�. LOCK_TIME ����� ������������� �����, ��� �� ��� ����� ����� ���� ��������, � �� ������� ������, ��� �� ���������� ���� ���� ������� � ������ �������
      */
    define('CACHER_BK_MEMREFILE_LOCK_TIME', 7);
    
    /**
      * MAX_LifeTIME - ������������ ����� ����� ����. �� ��������� 29 ����. ���� ������ set ������� $LifeTime=0, �� ����� ����������� 'expire' => (time()+self::MAX_LTIME)
      */
    define('CACHER_BK_MEMREFILE_MAX_LTIME', 2505600);
    
    /**
      * EXPIRE PREFIX - ������� ��� �������� ����� �� �������� ��������� ����
      */
    define('CACHER_BK_MEMREFILE_EXPR_PREF', '~xpr');

    /**
      * CACHE PATH - ���� � ���������� �������� ����. � ����� �������� ���� '/'
      */
    define('CACHER_BK_MEMREFILE_C_PATH',   '/tmp/safecache/');

    /**
      * TMP PATH - ���� � ����� �� ���������� �������
      */
    define('CACHER_BK_MEMREFILE_TMP_PATH', '/tmp');

    /**
      * CACHE EXTENTION - ���������� ��� ������ ����
      */
    define('CACHER_BK_MEMREFILE_C_EXT',    '.cache');




    /***************************************************************************
      *   Cacher_Backend_MemReCache0
      */

    
    /**
      * ������� ��� ������������ ����� ����������
      */
    define('CACHER_BK_MEMRECACHE0_LOCK_PREF', '~lock');
    
    /**
      * ����� ����� ����� ����������. ���� �� ����� ������������ ���� ������� �������� ����������,
      * �� ���������� ��������� ���������� � ������ �������� ����� ���������� �������� ��������� ��� LOCK_TIME ������.
      * � ������ ������� ���� ���� ���������� ������� �� ����, ��� ��� ����� ����������, �� ��������� ��������� ����� � ������������� �������� ���������� ��������.
      * �.�. LOCK_TIME ����� ������������� �����, ��� �� ��� ����� ����� ���� ��������, � �� ������� ������, ��� �� ���������� ���� ���� ������� � ������ �������
      */
    define('CACHER_BK_MEMRECACHE0_LOCK_TIME', 7);
    
    /**
      * MAX_LifeTIME - ������������ ����� ����� ����. �� ��������� 29 ����. ���� ������ set ������� $LifeTime=0, �� ����� ����������� 'expire' => (time()+self::MAX_LTIME)
      */
    define('CACHER_BK_MEMRECACHE0_MAX_LTIME', 2505600);




    /***************************************************************************
      *   Cacher_Backend_MemReCache
      */

    
    /**
      * ������� ��� ������������ ����� ����������
      */
    define('CACHER_BK_MEMRECACHE_LOCK_PREF', '~lock');
    
    /**
      * ����� ����� ����� ����������. ���� �� ����� ������������ ���� ������� �������� ����������,
      * �� ���������� ��������� ���������� � ������ �������� ����� ���������� �������� ��������� ��� LOCK_TIME ������.
      * � ������ ������� ���� ���� ���������� ������� �� ����, ��� ��� ����� ����������, �� ��������� ��������� ����� � ������������� �������� ���������� ��������.
      * �.�. LOCK_TIME ����� ������������� �����, ��� �� ��� ����� ����� ���� ��������, � �� ������� ������, ��� �� ���������� ���� ���� ������� � ������ �������
      */
    define('CACHER_BK_MEMRECACHE_LOCK_TIME', 7);
    
    /**
      * MAX_LifeTIME - ������������ ����� ����� ����. �� ��������� 29 ����. ���� ������ set ������� $LifeTime=0, �� ����� ����������� 'expire' => (time()+self::MAX_LTIME)
      */
    define('CACHER_BK_MEMRECACHE_MAX_LTIME', 2505600);
    
    /**
      * EXPIRE PREFIX - ������� ��� �������� ����� �� �������� ��������� ����
      */
    define('CACHER_BK_MEMRECACHE_EXPR_PREF', '~xpr');

?>
