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
/**
  *   __autoload
  */
   function __autoload($ClassName){
       require './src/class.'.strtolower($ClassName).'.php';
    }
################################################################################


 $m = Memstore::init();
 
 echo "<hr><pre>";
 var_export( $m->get('key1') );
 echo '</pre><hr>';
 
 echo "<hr><pre>";
 var_export($m->get(array(
                          'k1'=>'key1',
                          'k2'=>'key2',
                          'k3'=>'key3',
                          'k4'=>'key4',
                          'k5'=>'key5'
                          )));
 echo '</pre><hr>';
 
 echo "<hr>add<pre>";
 var_export($m->add('key-add3',true));
 echo '</pre><hr>';
 echo "<hr>key-add4<pre>";
 var_export($m->add('key-add4',17,5));
 echo '</pre><hr>';
 
 echo "<hr>set<pre>";
 var_export($m->set('key-sg1','пропро'));
 echo '</pre><hr>';
 echo "<hr><pre>";
 var_export($m->get('key-sg1'));
 echo '</pre><hr>';
 
 echo "<hr><pre>";
 var_export($m->set('key-sg2','абв', 10));
 echo '</pre><hr>';
 echo "<hr><pre>";
 var_export($m->get('key-sg2'));
 echo '</pre><hr>';
 
echo "<hr><pre>";
 var_export($m->get(array(1,2,3)));
echo '</pre><hr>';
 
 
echo "<hr><pre>";
var_export($m->set('key-sg3',555));
echo '</pre><hr>';
echo "<hr><pre>";var_export($m->get('key-sg3'));echo '</pre><hr>';
echo "<hr><pre>";
var_export($m->del('key-sg3'));
echo '</pre><hr>';
echo "<hr><pre>";var_export($m->get('key-sg3'));echo '</pre><hr>';
 

 # Числа
 # Кирилица
################################################################################

echo '<br>';
echo '<hr>memory usage: '.(memory_get_usage()/1024-$memory_get_usage_start) .'Kb<br>';
echo '<hr>memory peak_usage: '.(memory_get_peak_usage()/1024-$memory_get_usage_start) .'Kb<br>';
print_time('end script work');

?>