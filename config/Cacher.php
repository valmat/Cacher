<?php
################################################################################
/**
  *  [Cacher]
  *  config for class  Cacher
  */

    define('CACHER_PATH_TAGS',     $RootPath . '/classdata/cacher/tags.php');
    define('CACHER_PATH_SLOTS',    $RootPath . '/classdata/cacher/slots.php');
    define('CACHER_PATH_BACKENDS', $RootPath . '/classdata/cacher/backends/');

    # NameSpase prefix for cache key 
    define('CACHER_NAME_SPACE', 'dflt' );
    
    
    /*
     * ������������� ���� ����� �����������. ������� � ����� ���������� �����, ��� �������� ��������� ��������
     * ����: �������������� ����� ������������ �����.
     * �.�. ����� ������� �����: ���������� ���� ��������� �� �� ��������������� ���������� ������, � �� ��������� �� ������������ ���� SlotType
     * ������ ������ �� ���������� ������ � ������� ����������. � ���� ������ ��������� ��������� ����������� - ��� �� ������������� ������� �����,
     * � ����������� �������� � namespase SlotType
     */

    /**
      * ������� �� ������� ��� (� ������). ������ ����������.
      * ������� ��������� ��� ����� �������������� ������ � ������������ ������� �������� ���������, ����������� ������� ���������
      */
    define('CACHER_TYPE_SIMPLEST', 'MemCache' );
    
    /**
      * ������� �� ������� ��� (� ������). ������ ����������.
      * ������� ��������� ��� ����� �������������� ������ � ������������ ������� �������� ���������, ����������� ������� ���������
      */
    define('CACHER_TYPE_FAST', 'MemReCache' );

    /**
      * ���������� ������� (� �������� � ������), � � ���� ����� �������� ���������.
      * ����������� ����������� �� ���� �����������. �.�. ����������� ���������� �� ���� ������� � ������� (������) � �������� (�����) ���������.
      * ��������� ���������������� ���������� ������ ���� ������� (�� ���� ����������) ���� ������ �� ����� ������ ��������� ������
      * ������� ��������� ��� ������, ���������� ������� ����� (������� ������� � �.�.) � ����������� ������������� �������� ���������
      */
    define('CACHER_TYPE_SAFE', 'MemReFile' );

    /**
      * ������� (� �������� �� ������) ������ �����������, �� ����� ���������������� �� ��������� � self::FAST
      * ������� ��������� ��� ������, ��������� �������� ���������
      * ��� ������, ��� ������� ������������� ����� ������� �������� � �������� ������� ������������ ������� ��������������� ������.
      * (� ������ �������������������� ������� ����� ������� self::CHEAP = self::SAFE)
      */
    define('CACHER_TYPE_CHEAP', 'MemReFile' );

?>