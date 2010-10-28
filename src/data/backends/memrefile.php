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

class Cacher_Backend_MemReFile implements Cacher_Backend{
    
    private static $memcache=null;
    
    const MC_HOST   = 'unix:///tmp/memcached.socket';
    const MC_PORT   = 0;
    const NAME      = 'MemReFile';
    /**
      * сжатие memcache
      */
    const COMPRES   = false;//MEMCACHE_COMPRESSED;
    /**
      * Префикс для формирования ключа блокировки
      */
    const LOCK_PREF = '~lock';
    /**
      * Время жизни ключа блокировки. Если во время перестроения кеша процесс аварийно завершится,
      * то блокировка останется включенной и другие процессы будут продолжать выдавать протухший кеш LOCK_TIME секунд.
      * С другой стороны если срок блокировки истечет до того, как кеш будет перестроен, то возникнет состояние гонки и блокировочный механизм перестанет работать.
      * Т.е. LOCK_TIME нужно устанавливать таким, что бы кеш точно успел быть построен, и не слишком больши, что бы протухание кеша было заметно в выдаче клиенту
      */
    const LOCK_TIME = 7;
    /**
      * MAX_LifeTIME - максимальное время жизни кеша. По умолчанию 29 дней. Если методу set передан $LifeTime=0, то будет установлено 'expire' => (time()+self::MAX_LTIME)
      */
    const MAX_LTIME = 2505600;
    /**
      * EXPIRE PREFIX - префикс для хранения ключа со временем истечения кеша
      */
    const EXPR_PREF = '~xpr';
    
    /**
      * CACHE PATH - Путь к дериктории хранения кеша
      */
    const CACHE_PATH = '/tmp/safecache/';

    /**
      * TMP PATH - Путь к папке со временными файлами
      */
    const TMP_PATH = '/tmp';
    
    /**
      * CACHE EXTENTION - Расширение для файлов кеша
      */
    const CACHE_EXT = '.cache';
    
    /**
      * Флаг установленной блокировки
      * После установки этот флаг помечается в true
      * В методе set проверяется данный флаг, и только если он установлен, тогда снимается блокировка [self::$memcache->delete(self::LOCK_PREF . $CacheKey)]
      * Затем флаг блокировки должен быть снят: $this->is_locked = false;
      */
    private        $is_locked = false;
    
    function __construct() {
        if(null==self::$memcache){
           self::$memcache = new Memcache;
           self::$memcache->connect(self::MC_HOST, self::MC_PORT);
        }
    }
    
    /*
     * проверяем не установил ли кто либо блокировку
     * Если блокировка не установлена, пытаемся создать ее методом add, что бы предотвратить состояние гонки
     * function set_lock
     * @param $arg void
     */
    private function set_lock($CacheKey) {
        if( !($this->is_locked) && !(self::$memcache->get(self::LOCK_PREF . $CacheKey)) )
           $this->is_locked = self::$memcache->add(self::LOCK_PREF . $CacheKey,true,false,self::LOCK_TIME);
        return $this->is_locked;
    }
    
    function clearTag($tagKey){
        self::$memcache->set($tagKey, time(), false, 0 );
    }
    
    function get($CacheKey){
        # Если объекта в мем кеше не нашлось, то ищем в файле
        # В связи с скаким-то странным глюком в memcache красивая схема с мултизапросом не прошла.
        //if( false===( $c_arr = self::$memcache->get( Array( $CacheKey, self::EXPR_PREF . $CacheKey ) )) || !isset($c_arr[$CacheKey]) || !isset($c_arr[self::EXPR_PREF . $CacheKey]) ){
        if( false===( $cobj=self::$memcache->get($CacheKey) ) ){
           # Пытаемся установить блокировку
           # Если блокировку установили мы, то отправляемся перекешировать, иначе возвращаем устаревший объект из кеша
           if($this->set_lock($CacheKey))
             return false;
           # Пытаемся получить Кеш из файла
           if( file_exists(self::CACHE_PATH.$CacheKey.self::CACHE_EXT) )
              return unserialize(file_get_contents(self::CACHE_PATH.$CacheKey.self::CACHE_EXT));
           # Если файл кеша так же отсутствует, безусловно перекешируем
           return false;
        }
        
        # Если время жизни кеша истекло, то перекешируем с условием блокировки
        if( false===( $expire=self::$memcache->get(self::EXPR_PREF . $CacheKey) ) || $expire < time() ){
          # Пытаемся установить блокировку
          # Если блокировку установили мы, то отправляемся перекешировать, иначе возвращаем устаревший объект из кеша
          if($this->set_lock($CacheKey))
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
          if($this->set_lock($CacheKey))
            return false;
          return $cobj['data'];        
        }
        
        # Если кеш протух по тегам, то сообщаем об этом
        foreach($tags as $tag_k => $tag_v){
            if($tags_mc[$tag_k]>$tag_v){
              if($this->set_lock($CacheKey))
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
     * @param $CacheVal string,$CacheKey  string, $tags array, $LifeTime int
     */
    function set($CacheKey, $CacheVal, $tags, $LifeTime=self::MAX_LTIME){
        $thetime = time();
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
        
        self::$memcache->set(self::EXPR_PREF.$CacheKey, $expire, false, 0);
        self::$memcache->set($CacheKey, $cobj, self::COMPRES, 0);
        
        # Пишем кеш в файл
        if(!is_dir(self::CACHE_PATH))
           mkdir(self::CACHE_PATH, 0777);
           
        
        $tmp_file = tempnam(self::TMP_PATH, 'fctmp_');
        file_put_contents($tmp_file, serialize($CacheVal) );
        # Атомарно перемещаем файл с данными кеша в файловый кеш
        rename($tmp_file,self::CACHE_PATH.$CacheKey.self::CACHE_EXT);
        
        # Снимаем блокировку
        if($this->is_locked){
            $this->is_locked = false;
            self::$memcache->delete(self::LOCK_PREF . $CacheKey);
        }
        
        return $CacheVal;
    }
    
    /*
     * Полная очистка текущего кеша без поддержки переекеширования. Если нужно удаление с перекешированием, то нужно использовать теги
     * function del
     * @param $CacheKey  string
     */
    function del($CacheKey){
        //return self::$memcache->delete($CacheKey);
        //return self::$memcache->set(self::EXPR_PREF.$CacheKey, 0, false, 0);
        return self::$memcache->delete(self::EXPR_PREF.$CacheKey);
    }
    
}

?>
