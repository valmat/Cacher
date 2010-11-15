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
 //require './config/CacherTag.php';
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
           $this->id = rand(1,20000);
       }
    }
    
    

    function GetFromAnyExternal(User $User){
        return Array('username','userid'=>$User->id, date('h:i:s A') );
    }
    
    $User = new User();
    //Cacher::Slot('User',$User);
    
    $slot = Cacher::create('User',$User);

    if (false === ($CacheData = $slot->get()))// ���� ������ �� ���� �������� �� �������...
    { 
         $CacheData = GetFromAnyExternal($User);        // �������� ������ �� �������� ���������
         $slot->addTag(Cacher_Tag::create('SmplTag',  $User)); // ������� � ������� ��������� ����� ��� � ����� ����� ������������ � ���
         $slot->addTag(Cacher_Tag::create('SmplTag1', $User)); // ������� � ������� ��������� ����� ��� � ����� ����� ������������ � ���
         
         Cacher_Tag::create('SmplTag', $User)->getKey();
         
         //sleep(1);// hard data
         
         echo '<hr><font color=blue>�������� ������</font><hr>';
           
         $slot->set($CacheData);                                    // �������� ������
         
     }
    //$slot->del();
    
    //Cacher_Tag::create('SmplTag',$User)->clear();
    
    //Cacher::newTag('SmplTag1',$User)->clear();
    
    //Cacher::newTag('AniTagData2',AniTagDataObj1)->clear()        // ������� ��� ����
echo '<hr>����������� ������:<pre>';
var_export($CacheData);
echo '</pre><hr>';


################################################################################

echo '<br>';
echo '<hr>memory usage: '.(memory_get_usage()/1024-$memory_get_usage_start) .'��<br>';
    print_time('end script work');

?>