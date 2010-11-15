<?php

/**
 * ��������� ������� ��� �����������.
 * 
 */

abstract class Cacher_Backend
 {
    private $key;
    private $nameSpace;
        
    /*
     * �������� �������� ���� ���� ����, ��� false, ���� �����������.
     * function get
     */
    abstract function get();
    /*
     * ���������� ������ � ���
     * function set
     * @param $CacheVal mixed   ������ ����
     * @param $tags     array   ������ ����� �����������
     * @param $LifeTime int     ����� ����� ����
     */
    abstract function set($CacheVal, $tags, $LifeTime);
    /*
     * �������� ��� �� �����
     * function del
     */
    abstract function del();
    /*
     * ��� ������������ �����. ������ � ����� ������ ��������� ������ � �����
     * tagType()
     * @param void
     * @return string Cache tag type throw CacheTagTypes namespace
     */
    abstract function tagType(); /*{return CacheTagTypes::FAST;}*/
    
    /*
     * __construct()
     * @param void
     */
    function __construct($CacheKey, $nameSpace) {
        $this->key       = $CacheKey;
        $this->nameSpace = $nameSpace;
    }
 }


?>