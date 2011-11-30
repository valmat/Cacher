<?php

/*
 * class Cacher_Backend_MemReFile
 * 
 * Бэкенд класса Cacher для кеширования в memcache и в файловой системе c безопасным перекешированием.
 * Для перекеширования используются блокировки на memcache.
 * Таким образом исключается состаяние гонки и обновлением кеша занимается только один процесс.
 * Тем временем другие процессы временно используют устаревший кеш.
 * Как и в Cacher_Backend_Memcache используются теги.
 * К кешируемому объекту добавляется параметр 'expire'. таким образом за истечением срока годности кеша должен следить не memcache,
 * а сам класс.
 * В отличии от Cacher_Backend_MemReCache0 в данном классе при удалении кеша по ключу происходит не фактическое удаление кеша на уравне
 * Memcache, а лишь сброс параметра Expire. За счет этого возможен сброс кеша без использования тегов с возможностью безгоночного перекеширования
 * Каждый ключ кеша дублируется в файловой системе.
 * За счет этого не тратятся ресурсы на разогрев кеша в случае его потери в виртуальной памяти или врезультате ребута.
 * Кеширование в фаловой системе устроено самым примитивным образом, что бы мнимизировать нарузку на сервер.
 * То есть кеш читается из файла только, если потерян кеш в памяти (конкретно в memcache)
 * Для обновления файлового кеша вместо блокировки (которая помсути избыточна) используется атомарная функция rename()
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 *  CacheObj(in Memcache):
 *  'cache_key'=Array(
 *        'data' => <cached data>
 *        'tags' => Array(
 *                       'tag1' => ...,
 *                       'tag2' => ...,
 *                       'tag3' => ...
 *                       )
 *                  );
 *  CacheObj(in File):
 *  <cached data>
 * LockFlag:
 * '~lock'.'cache_key' = true/false
 * Expire:
 * '~xpr'.'cache_key' = ...
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * 
 */

require_once CONFIG_Cacher::PATH_BACKENDS . 'locks/lock.memcache.php';

class Cacher_Backend_MemReFile extends Cacher_Backend {
    
    private static $memcache=null;
    
    /**
      * MAX_LifeTIME - максимальное время жизни кеша. По умолчанию 29 дней. Если методу set передан $LifeTime=0, то будет установлено 'expire' => (time()+self::MAX_LTIME)
      */
    const MAX_LTIME = CONFIG_Cacher_BK_MemReFile::MAX_LTIME;
    /**
      * EXPIRE PREFIX - префикс для хранения ключа со временем истечения кеша
      */
    const EXPR_PREF = CONFIG_Cacher_BK_MemReFile::EXPR_PREF;
    /**
      * CACHE PATH - Путь к дериктории хранения кеша
      */
    const CACHE_PATH = CONFIG_Cacher_BK_MemReFile::CACHE_PATH;
    /**
      * Cache file path depth - Глубина вложенности файлов с кешем
      */
    const CF_DEPTH   = CONFIG_Cacher_BK_MemReFile::CF_DEPTH;
    /**
     *  NameSpase prefix for cache key
     */
    const  NAME_SPACE   = Cacher::NAME_SPACE;
    
    /**
      * Имя используемого класса блокировки
      */
    const LOCK_NAME = 'Cacher_Lock_Memcache';
    
    /**
      * Полный путь (относительно self::CACHE_PATH) к файловому кешу для данного ключа
      */
    private $fullpath = '';
    
    /**
      * Массив путей к файловому кешу в иерархии поддиректорий
      */
    private $patharr = Array();
    
    function __construct($CacheKey) {
        parent::__construct($CacheKey);
        self::$memcache = Mcache::init();
    }
    
    public function get() {
        $lock = self::LOCK_NAME;
        # Если объекта в мем кеше не нашлось, то ищем в файле
        # В связи с скаким-то странным глюком в memcache красивая схема с мултизапросом не прошла.
        
        if( false===( $c_arr = self::$memcache->get( Array( $this->key, self::EXPR_PREF . $this->key ) )) || !isset($c_arr[$this->key]) ){
        //if( false===( $cobj=self::$memcache->get($this->key) ) ){
            # Пытаемся установить блокировку
            # Если блокировку установили мы, то отправляемся перекешировать, иначе возвращаем устаревший объект из кеша
            if($lock::set($this->key))
                return false;
            # Пытаемся получить Кеш из файла
            $this->getPath();
            if( file_exists( $this->fullpath ) )
                return unserialize(file_get_contents( $this->fullpath ));
            # Если файл кеша так же отсутствует, безусловно перекешируем
            return false;
        }
        
        return self::mainGet($this->key, $c_arr);
    }
    
    /*
     * Получение кеша для мультиключа
     * function get
     */
    static function multiGet($keys){
        !self::$memcache && (self::$memcache = Mcache::init());
        $expir_keys  = array_map ( 'self::expirKey' , $keys );
        
        # Если объекта в кеше не нашлось, то безусловно перекешируем
        if( false===( $c_arr = self::$memcache->get( array_merge ( $expir_keys, $keys ) )) ){
            return false;
        }
        
        $rez = array();
        foreach($keys as $k => $key) {
            # Если объекта в кеше не нашлось, то безусловно перекешируем
            if(!isset($c_arr[$key]) ) {
                $rez[$k] = NULL;
            } else {
                $cobj = self::mainGet($key, $c_arr);
                $rez[$k] = (false===$cobj)?NULL:$cobj;
            }
        }
        return $rez;
    }
        
    /*
     * function mainGet
     * @param $key string
     * @param $cobj array
     */
    private static function mainGet($key, &$c_arr) {
        $lock = self::LOCK_NAME;
        $cobj   = $c_arr[$key];
        
        # Если время жизни кеша истекло, то перекешируем с условием блокировки
        if(!isset($c_arr[self::EXPR_PREF . $key]) || $c_arr[self::EXPR_PREF . $key]/*expire*/ < time() ){
            # Пытаемся установить блокировку
            # Если блокировку установили мы, то отправляемся перекешировать, иначе возвращаем устаревший объект из кеша
            if($lock::set($key))
                return false;
            return $cobj['data'];
        }
        $tags = $cobj['tags'];
        $tags_cnt = count($tags);
        
        # Если тегов нет, то просто отдаем объект. Тогда дальше можно считать 0!=$tags_cnt
        if(0==$tags_cnt)
            return $cobj['data'];
        
        $tags_mc = self::$memcache->get( array_keys($cobj['tags']) );
        # Если в кеше утеряна информация о каком либо теге, то сбрасывается кеш ассоциированный с этим тегом
        if( count($tags_mc)!= $tags_cnt){
            if($lock::set($key))
                return false;
            return $cobj['data'];
        }
        
        # Если кеш протух по тегам, то сообщаем об этом
        foreach($tags as $tag_k => $tag_v){
            if($tags_mc[$tag_k]>$tag_v){
                if($lock::set($key))
                    return false;
                return $cobj['data'];
            }
        }
        
        return $cobj['data'];
    }
        
    /*
     * Установка значения кеша по ключу вместе с тегами и указанием срока годности кеша
     * Проверяется установка блокировки
     * function set
     * @param $CacheVal string,  string, $tags array, $LifeTime int
     */
    function set($CacheVal, $tags, $LifeTime){
        $thetime = time();
        $lock = self::LOCK_NAME;
        # проверяем наличие тегов и при необходимости устанавливаем их
        $tags_cnt = count($tags);
        
        if( 0==$tags_cnt || false===($tags_mc = self::$memcache->get( $tags )) )
           $tags_mc = Array();
        
        if( $tags_cnt>0 && count($tags_mc)!= $tags_cnt)
          {
            for($i=0;$i<$tags_cnt;$i++)
               if(!isset($tags_mc[$tags[$i]]))
                  {
                    $tags_mc[$tags[$i]] = $thetime;
                    self::$memcache->set( $tags[$i], $thetime, false, 0 );
                  }
          }
        $expire = (((0==$LifeTime)?(self::MAX_LTIME):$LifeTime)+$thetime);
        $cobj = Array(
                      'data' => $CacheVal,
                      'tags' => $tags_mc
                     );
        
        self::$memcache->set(self::EXPR_PREF . $this->key, $expire, false, 0);
        self::$memcache->set($this->key, $cobj, Mcache::COMPRES, 0);
        
        # Пишем кеш в файл
        # Если блокировку установил текущий процесс, то пишем в файл
        if($lock::get($this->key)){
            $this->getPath();
            $thedir = self::CACHE_PATH;
            for($i=0; $i<=self::CF_DEPTH; $i++){
                if(!is_dir($thedir .= '/' . $this->patharr[$i]))
                   mkdir($thedir, 0700);
            }
            $tmp_file = tempnam($thedir, 'fctmp_');
            file_put_contents($tmp_file, serialize($CacheVal) );
            # Атомарно перемещаем файл с данными кеша в файловый кеш
            rename($tmp_file, $this->fullpath);
        }        
        
        # Снимаем блокировку
        $lock::del($this->key);
        
        return $CacheVal;
    }
    
    /*
     * Полная очистка текущего кеша без поддержки переекеширования. Если нужно удаление с перекешированием, то нужно использовать теги
     * function del
     */
    function del(){
        return self::$memcache->delete(self::EXPR_PREF . $this->key, 0);
    }
    
    /*
     * tagsType()
     * @param void
     * @return string Cache tag type throw CacheTagTypes namespace
     */
    function tagsType() {
        return CacheTagTypes::MC;
    }
    
    /*
     * Устанавливает $patharr и $fullpath
     * function getPath
     * @param void
     * @return void
     */
    private function getPath() {
        if(''==$this->fullpath){
            $this->patharr[] = self::NAME_SPACE;
            $sha1 = sha1($this->key);
            
            for($i=0; $i<self::CF_DEPTH; $i++){
                $this->patharr[] = $sha1[2*$i] . $sha1[2*$i+1];
            }
            $this->patharr[] = substr($sha1, 2*self::CF_DEPTH);
            $this->fullpath = self::CACHE_PATH .'/'. implode('/',$this->patharr);
        }
    }
    
    /*
     * function expirKey
     * @param $key
     */
    private static function expirKey($key) {
        return self::EXPR_PREF . $key;
    }
    
}

