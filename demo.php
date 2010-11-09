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
global $time_start;
$time_start = microtime_float($cmnt = '');

function print_time($cmnt = ''){
    static $cal_nom = 0;
    global $time_start;
    
    $cal_nom++;
    echo '<hr>time: '.( (microtime_float() - $time_start)*1000 )." ms ($cal_nom) [$cmnt]<br>";
   }

################################################################################ 
 
 require './config/Cacher.php';
 require './config/Cacher_Backends.php';
 require './config/CacherTag.php';
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
    print_time();
    Cacher::Slot('User',$User);
    echo Cacher::$BackendName;
    
    print_time();


    if (false === ($CacheData = Cacher::get()))// ���� ������ �� ���� �������� �� �������...
    { 
         $CacheData = GetFromAnyExternal($User);        // �������� ������ �� �������� ���������
         //Cacher::addTag(Cacher_Tag::create('SmplTag',  $User)); // ������� � ������� ��������� ����� ��� � ����� ����� ������������ � ���
         Cacher::addTag(Cacher_Tag::create('SmplTag1', $User)); // ������� � ������� ��������� ����� ��� � ����� ����� ������������ � ���
         
         //Cacher_Tag::create('SmplTag1', $User)->getKey();
         
         sleep(1);// hard data
         
         echo '<hr><font color=blue>�������� ������</font><hr>';
           
         Cacher::set($CacheData);                                    // �������� ������
         
     }
    //Cacher::del();
    //Cacher::newTag('SmplTag',$User)->clear();
    //Cacher::newTag('SmplTag1',$User)->clear();
    
    //Cacher::newTag('AniTagData2',AniTagDataObj1)->clear()        // ������� ��� ����
print_time();
echo '<hr>����������� ������:<pre>';
var_export($CacheData);
echo '</pre><hr>';


################################################################################

echo '<br>';
echo '<hr>memory usage: '.(memory_get_usage()/1024-$memory_get_usage_start) .'��<br>';
    print_time();

?>