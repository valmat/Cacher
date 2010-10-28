<?php
 echo '<hr>memory_get_usage: '.(memory_get_usage()/1024) .'��<br>';
 require './config/config.php';

################################################################################
/**
  *   __autoload
  */
   function __autoload($ClassName){
       //require_once PATH_CLASS.'/class.'.strtolower($ClassName).'.php';
       require './src/class.'.strtolower($ClassName).'.php';
    }
    
 //echo  Cacher::name('test');

 
 //echo  SimplTempl::Plug('test',0,5);
 //echo  SimplTempl::Plug('test1');

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

    function GetFromAnyExternal(User $User){
        return Array('username','userage'=>20, date('h:i:s A') );
    }
    $User = new User();
    Cacher::Slot('User',$User);
    echo Cacher::$BackendName;


/*
$CacheData = GetFromAnyExternal($User);        // �������� ������ �� �������� ���������
Cacher::addTag(Cacher::newTag('SmplTag',$User)); // ������� � ������� ��������� ����� ��� � ����� ����� ������������ � ���
Cacher::addTag(Cacher::newTag('SmplTag1',$User)); // ������� � ������� ��������� ����� ��� � ����� ����� ������������ � ���
Cacher::set($CacheData);
//Cacher::newTag('SmplTag1',$User)->clear();
exit;
*/
$memcache = new Memcache();
$memcache->connect('unix:///tmp/memcached.socket', 0);
//$memcache->set('cachecnt',0,false,(5*3600));
//$memcache->flush();exit;



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

echo '<hr>����������� ������:<pre>';
var_export($CacheData);
echo '</pre><hr>';

//echo '<hr><pre>';var_export(get_defined_functions());echo '</pre><hr>';
echo '<hr>'.$memcache->get('cachecnt'); 
echo '<hr>memory_get_usage: '.(memory_get_usage()/1024) .'��<br>';

?>