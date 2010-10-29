<?php
/*******************************************************************************
  *  [Cacher]
  *  config for class  Cacher
  */

    define('CACHER_PATH_TAGS',     './src/data/tags.php');
    define('CACHER_PATH_SLOTS',    './src/data/slots.php');
    define('CACHER_PATH_BACKENDS', './src/data/backends/');

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
