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
    
    
    $usercount = 50;
    $users= array();
    for($i=0; $i<$usercount; $i++) {
        //$id = mt_rand(1,1000);
        $id = $usercount-$i;
        $users[$id] = new User($id);
    }
    
    function GetFromAnyExternal($arr, $users){
        
        $r = array();
        foreach($arr as $key) {
           $r[$key] =  array('_id'=>$users[$key]->id, date('h:i:s A') );
        }
        return $r;
    }
    
    
    $toFill = array();
    $keys = array_keys($users);
    $slots = Cacher::create('Test', $keys);
    $CacheData = array();
    
    //echo "<hr><pre>";var_export($slots);echo '</pre><hr>'; exit;
    
    foreach($slots as $key => $slot) {
        if( !($CacheData[$key] = $slot->get()) ) {
            $toFill[] = $key;
        }
    }
    $rez = GetFromAnyExternal($toFill, $users);
    foreach($toFill as $key) {
        //$slot->addTag(Cacher_Tag::create('SmplTag',  $User)); // Создаем и сразуже добавляем новый тег к слоту перед сохрананеием в кеш
        //$slot->addTag(Cacher_Tag::create('SmplTag1', $User)); // Создаем и сразуже добавляем новый тег к слоту перед сохрананеием в кеш
        
        //echo "<hr>setTag: ", Cacher_Tag::create('SmplTag', $key)->getKey();
        
        
        $slots[$key]->addTag( Cacher_Tag::create('SmplTag', $key) );
        
        
        //sleep(1);// hard data
        
        $val =$rez[$key];
        echo "<br><font color=blue>to cache($key)</font><br>";
        $slots[$key]->set($val);
        $CacheData[$key] = $val;
    }
    
    Cacher_Tag::create('SmplTag', 50)->clear();
    
    //$slots[10]->del();
    
    echo "<hr><pre>";var_export($slots);echo '</pre><hr>';

    
    
    //$slot->del();
    
    //Cacher_Tag::create('SmplTag',$User)->clear();
    
    //Cacher::newTag('SmplTag1',$User)->clear();
    
    //Cacher::newTag('AniTagData2',AniTagDataObj1)->clear()        // Очищаем кеш тега

//echo '<hr>Cached data:<pre>';var_export($CacheData);echo '</pre><hr>';


################################################################################

echo '<br>';
echo '<hr>memory usage: '.(memory_get_usage()/1024-$memory_get_usage_start) .'Kb<br>';
echo '<hr>memory peak_usage: '.(memory_get_peak_usage()/1024-$memory_get_usage_start) .'Kb<br>';
    print_time('end script work');

?>