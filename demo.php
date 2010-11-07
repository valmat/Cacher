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


    if (false === ($CacheData = Cacher::get()))// Если данные из кеша получить не удалось...
    { 
         $CacheData = GetFromAnyExternal($User);        // Получаем данные из внешнего хранилища
         Cacher::addTag(Cacher::newTag('SmplTag',$User)); // Создаем и сразуже добавляем новый тег к слоту перед сохрананеием в кеш
         Cacher::addTag(Cacher::newTag('SmplTag1',$User)); // Создаем и сразуже добавляем новый тег к слоту перед сохрананеием в кеш
         
         //$memcache->decrement('cachecnt');
         sleep(2);// hard data
         $memcache->increment('cachecnt');
         
         
         echo '<hr><font color=blue>Кешируем данные</font><hr>';
    
           
         Cacher::set($CacheData);                                    // Кешируем данные
         
     }
    //Cacher::del();
    //Cacher::newTag('SmplTag',$User)->clear();
    //Cacher::newTag('SmplTag1',$User)->clear();
    
    //Cacher::newTag('AniTagData2',AniTagDataObj1)->clear()        // Очищаем кеш тега
print_time($time_start);
echo '<hr>Кешированый объект:<pre>';
var_export($CacheData);
echo '</pre><hr>';


################################################################################

echo '<br>';
echo '<hr>memory usage: '.(memory_get_usage()/1024-$memory_get_usage_start) .'Кб<br>';
    print_time($time_start);

?>