<?php
/*
    * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
    * Пример использования:
    * 
    * define AnyObj // может быть класс, массив или дрогой объект. На основании эого объекта слот функция вычислит ключь и, возможно другиие параметры (бэкэнд и время жизни).
    * Cacher::Slot('AniObj',AniObj); // Инициализируем слот кеширования. Первый параметр для определения имени слота, второй - наш объект
    *
    * Получаем данные  
    * if (false === ($CacheData = Cacher::get())) { // Если данные из кеша получить не удалось...
    *     $CacheData = GetFromAnyExternal();        // Получаем данные из внешнего хранилища
    *     Cacher::addTag(Cacher::newTag('AniTagData',AniTagDataObj)); // Создаем и сразуже добавляем новый тег к слоту перед сохрананеием в кеш
    *     $tag2 = Cacher::newTag('AniTagData2',AniTagDataObj1)        // Создаем новый тег
    *     Cacher::addTag($tag2);                                      // добавляем новый тег к слоту перед сохрананеием в кеш
    *     Cacher::set($CacheData);                                    // Кешируем данные
    * }
    * ...
    * ...
    * Если затем нужно сбросить какой нибудь тег, то нужно будет сделать так:
    * Cacher::newTag('AniTagData2',AniTagDataObj1)->clear()        // Очищаем кеш тега
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
           $this->id = $id;//rand(1,20000);
       }
    }
    
    

    function GetFromAnyExternal(User $User){
        return Array('username','userid'=>$User->id, date('h:i:s A') );
    }
    
    
    echo "<hr><pre>";
    var_export(Cacher_Tag::create('SmplTag', 51)->getKey());
    echo '</pre><hr>';
    
    $User1 = new User(1);
    $User2 = new User(2);
    $User3 = new User(3);
    //Cacher::Slot('User',$User);
    $keys = array($User1->id,$User2->id,$User3->id);
    $slot = Cacher::create('Test', $keys);
    $CacheData = $slot->get();
    foreach($CacheData as $key => $rez) {
        if (false === $rez) {
             $CacheData = GetFromAnyExternal($User);        // Получаем данные из внешнего хранилища
             //$slot->addTag(Cacher_Tag::create('SmplTag',  $User)); // Создаем и сразуже добавляем новый тег к слоту перед сохрананеием в кеш
             //$slot->addTag(Cacher_Tag::create('SmplTag1', $User)); // Создаем и сразуже добавляем новый тег к слоту перед сохрананеием в кеш
             
             //Cacher_Tag::create('SmplTag', $User)->getKey();
             
             //sleep(1);// hard data
             
             echo '<hr><font color=blue>to cache</font><hr>';
               
             $slot->set($rez, $key);
        }
    }
    
    //$slot->del();
    
    //Cacher_Tag::create('SmplTag',$User)->clear();
    
    //Cacher::newTag('SmplTag1',$User)->clear();
    
    //Cacher::newTag('AniTagData2',AniTagDataObj1)->clear()        // Очищаем кеш тега
echo '<hr>Cached data:<pre>';
var_export($CacheData);
echo '</pre><hr>';


################################################################################

echo '<br>';
echo '<hr>memory usage: '.(memory_get_usage()/1024-$memory_get_usage_start) .'Kb<br>';
echo '<hr>memory peak_usage: '.(memory_get_peak_usage()/1024-$memory_get_usage_start) .'Kb<br>';
    print_time('end script work');

?>