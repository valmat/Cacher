<?php

/**
 * ��������� ������� ��� �����������.
 * 
 */

interface Cacher_Backend
 {
    /*
     * �������� �������� ���� ���� ����, ��� false, ���� �����������.
     * function get
     */
    function get();
    /*
     * ���������� ������ � ���
     * function set
     * @param $CacheVal mixed   ������ ����
     * @param $tags     array   ������ ����� �����������
     * @param $LifeTime int     ����� ����� ����
     */
    function set($CacheVal, $tags, $LifeTime=0);
    /*
     * �������� ��� �� �����
     * function del
     */
    function del();
 }
?>