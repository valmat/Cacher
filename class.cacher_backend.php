<?php

/**
 * ��������� ������� ��� �����������.
 * 
 */

interface Cacher_Backend
 {
     /*
     * ������� ��� �� ����
     * function clearTag
     * @param $tagKey   string
     */
    function clearTag($tagKey);
    /*
     * �������� �������� ���� ���� ����, ��� false, ���� �����������.
     * function get
     * @param $CacheKey string  ���� ����
     */
    function get($CacheKey);
    /*
     * ���������� ������ � ���
     * function set
     * @param $CacheKey string  ���� ����
     * @param $CacheVal mixed   ������ ����
     * @param $tags     array   ������ ����� �����������
     * @param $LifeTime int     ����� ����� ����
     */
    function set($CacheKey, $CacheVal, $tags, $LifeTime=0);
    /*
     * �������� ��� �� �����
     * function del
     * @param $CacheKey string
     */
    function del($CacheKey);
 }
?>