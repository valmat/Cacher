<?php
/*
    * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
    * ������ �������������:
    * 
    * define AnyObj // ����� ���� �����, ������ ��� ������ ������. �� ��������� ���� ������� ���� ������� �������� ����� �, �������� ������� ��������� (������ � ����� �����).
    * Cacher::Slot('AniObj',AniObj); // �������������� ���� �����������. ������ �������� ��� ����������� ����� �����, ������ - ��� ������
    *
    * �������� ������  
    * if (false === ($CacheData = Cacher::get())) { // ���� ������ �� ���� �������� �� �������...
    *     $CacheData = GetFromAnyExternal();        // �������� ������ �� �������� ���������
    *     Cacher::addTag(Cacher::newTag('AniTagData',AniTagDataObj)); // ������� � ������� ��������� ����� ��� � ����� ����� ������������ � ���
    *     $tag2 = Cacher::newTag('AniTagData2',AniTagDataObj1)        // ������� ����� ���
    *     Cacher::addTag($tag2);                                      // ��������� ����� ��� � ����� ����� ������������ � ���
    *     Cacher::set($CacheData);                                    // �������� ������
    * }
    * ...
    * ...
    * ���� ����� ����� �������� ����� ������ ���, �� ����� ����� ������� ���:
    * Cacher::newTag('AniTagData2',AniTagDataObj1)->clear()        // ������� ��� ����
    * 
    * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * 
*/
################################################################################
$memory_get_usage_start = (memory_get_usage()/1024);

function microtime_float(){
    list($usec, $sec) = explode(" ", microtime());
    return ((float)$usec + (float)$sec);
   }

function print_time($time_start){
    static $cal_nom = 0;
    $cal_nom++;
    echo '<hr>time: '.( (microtime_float() - $time_start)*1000 )." ms ($cal_nom)<br>";
   }
$time_start = microtime_float();
################################################################################ 
 
 require './config/Cacher.php';
 require './config/Cacher_Backends.php';
 require './config/base.php';
 
################################################################################
/**
  *   __autoload
  */
   function __autoload($ClassName){
       require './src/class.'.strtolower($ClassName).'.php';
    }
################################################################################
    /*
     * class User
     */
    class User {
       
       public $id;
       function __construct($id=5) {
           $this->id = $id;
       }
    }

    function GetFromAnyExternal(User $User){
        return Array('username','userage'=>20, date('h:i:s A') );
    }
    
    $User = new User();
    print_time($time_start);
    Cacher::Slot('User',$User);
    print_time($time_start);
    echo Cacher::$BackendName;
    
    print_time($time_start);


    if (false === ($CacheData = Cacher::get()))// ���� ������ �� ���� �������� �� �������...
    { 
         $CacheData = GetFromAnyExternal($User);        // �������� ������ �� �������� ���������
         Cacher::addTag(Cacher::newTag('SmplTag',$User)); // ������� � ������� ��������� ����� ��� � ����� ����� ������������ � ���
         Cacher::addTag(Cacher::newTag('SmplTag1',$User)); // ������� � ������� ��������� ����� ��� � ����� ����� ������������ � ���
         
         //$memcache->decrement('cachecnt');
         sleep(2);// hard data
         $memcache->increment('cachecnt');
         
         
         echo '<hr><font color=blue>�������� ������</font><hr>';
    
           
         Cacher::set($CacheData);                                    // �������� ������
         
     }
    //Cacher::del();
    //Cacher::newTag('SmplTag',$User)->clear();
    //Cacher::newTag('SmplTag1',$User)->clear();
    
    //Cacher::newTag('AniTagData2',AniTagDataObj1)->clear()        // ������� ��� ����
print_time($time_start);
echo '<hr>����������� ������:<pre>';
var_export($CacheData);
echo '</pre><hr>';


################################################################################

echo '<br>';
echo '<hr>memory usage: '.(memory_get_usage()/1024-$memory_get_usage_start) .'��<br>';
    print_time($time_start);

?>