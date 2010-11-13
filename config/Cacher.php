<?php
################################################################################
/**
  *  [Cacher & Cacher_Tag]
  *  config for class  Cacher and Cacher_Tag
  */


    class CONFIG_Cacher {
        # NameSpase prefix for cache key
        const NAME_SPACE    = 'dflt';
        
        # NameSpase prefix for tag key
        const TAG_NM_SPACE  = 'dflt_k';
        
        const PATH_TAGS     = './src/data/tags.php';
        const PATH_SLOTS    = './src/data/slots.php';
        const PATH_BACKENDS = './src/data/strategy/';
        
        
       
    }
    
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
        //const FAST = 'MemReCache';
        const FAST = 'MemReCache0';
    
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