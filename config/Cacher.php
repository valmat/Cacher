<?php
################################################################################
/**
  *  [Cacher_Tag]
  *  config for class  Cacher_Tag
  */

    # NameSpase prefix for tag key
    define('CACHER_TAG_NAME_SPACE', 'dflt_k' );

################################################################################
/**
  *  [Cacher]
  *  config for class  Cacher
  */

    # NameSpase prefix for cache key 
    define('CACHER_NAME_SPACE', 'dflt' );

    define('CACHER_PATH_TAGS',     './src/data/tags.php');
    define('CACHER_PATH_SLOTS',    './src/data/slots.php');
    define('CACHER_PATH_BACKENDS', './src/data/strategy/');
    
    /***************************************************************************
     * ������������� ���� ����� �����������. ������� � ����� ���������� �����, ��� �������� ��������� ��������
     * ����: �������������� ����� ������������ �����.
     * �.�. ����� ������� �����: ���������� ���� ��������� �� �� ��������������� ���������� ������, � �� ��������� �� ������������ ���� CacheTypes
     * ������ ������ �� ���������� ������ � ������� ����������. � ���� ������ ��������� ��������� ����������� - ��� �� ������������� ������� �����,
     * � ����������� �������� � namespase CacheTypes
     */
    
    class CacheTypes{
        
        /**
          * ������� �� ������� ��� (� ������). ������ ����������.
          * ������� ��������� ��� ����� �������������� ������ � ������������ ������� �������� ���������, ����������� ������� ���������
          */
        const SIMPLEST = 'MemCache';
        
        /**
          * ������� �� ������� ��� (� ������). ������ ����������.
          * ������� ��������� ��� ����� �������������� ������ � ������������ ������� �������� ���������, ����������� ������� ���������
          * ��� ������������ ���� �������� ��������� ������ �� ��� ���, ���� ����� ������ �� ����� ��������.
          * ��������� ����� � ������������� ������ �� ������������ ���� ���������
          */
        const FAST = 'MemReCache';
    
        /**
          * ���������� ������� (� �������� � ������), � � ���� ����� �������� ���������.
          * ����������� ����������� �� ���� �����������. �.�. ����������� ���������� �� ���� ������� � ������� (������) � �������� (�����) ���������.
          * ��������� ���������������� ���������� ������ ���� ������� (�� ���� ����������) ���� ������ �� ����� ������ ��������� ������
          * ������� ��������� ��� ������, ���������� ������� ����� (������� ������� � �.�.) � ����������� ������������� �������� ���������
          */
        const SAFE = 'MemReFile';
    
        /**
          * ������� (� �������� �� ������) ������ �����������, �� ����� ���������������� �� ��������� � self::FAST
          * ������� ��������� ��� ������, ��������� �������� ���������
          * ��� ������, ��� ������� ������������� ����� ������� �������� � �������� ������� ������������ ������� ��������������� ������.
          * (� ������ �������������������� ������� ����� ������� self::CHEAP = self::SAFE)
          */
        const CHEAP = 'MemReFile';        
    }

?>
