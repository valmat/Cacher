<?php

/*
 * class Cacher_Backend_MemReCache
 * 
 * Бэкенд класса Cacher для кеширования в memcache c безопасным перекешированием.
 * Для перекеширования используются блокировки.
 * Таким образом исключается состаяние гонки и обновлением кеша занимается только один процесс.
 * Тем временем другие процессы временно используют устаревший кеш.
 * Как и в Cacher_Backend_Memcache используются теги.
 * К кешируемому объекту добавляется параметр 'expire'. таким образом за истечением срока годности кеша должен следить не memcache,
 * а сам класс.
 * В отличии от Cacher_Backend_MemReCache0 в данном классе при удалении кеша по ключу происходит не фактическое удаление кеша на уравне
 * Memcache, а лишь сброс параметра Expire. За счет этого возможен сброс кеша без использования тегов с возможностью безгоночного перекеширования
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 *  CacheObj:
 *  'cache_key'=Array(
 *        'data' => ...
 *        'tags' => Array(
 *                       'tag1' => ...,
 *                       'tag2' => ...,
 *                       'tag3' => ...
 *                       )
 *                  );
 * LockFlag:
 * '~lock'.'cache_key' = true/false
 * Expire:
 * '~xpr'.'cache_key' = ...
 * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * * *
 * 
 */

class Cacher_Backend_MemReCache implements Cacher_Backend{
    
    private static $memcache=null;
    
    const MC_HOST   = 'unix:///tmp/memcached.socket';
    const MC_PORT   = 0;
    const NAME      = 'MemReCache';
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
    const EXPR_PREF = 'xpr_';
    
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
        # если объекта в кеше не нашлось, то безусловно перекешируем
        
        //self::$memcache->flush();exit;
        echo '<hr><font color="blue"><pre>';
        var_export( self::$memcache->get( Array( $CacheKey, self::EXPR_PREF . $CacheKey ) ) );
        echo '</pre></font><hr>';






        $cobj   = self::$memcache->get($CacheKey);
        $expire = self::$memcache->get(self::EXPR_PREF . $CacheKey);
        
echo '<hr><pre>';
var_export($cobj);
echo '</pre><hr>';
echo '<hr><pre>';
var_export($expire);
echo '</pre><hr>';


           $carr = self::$memcache->get( Array( $CacheKey, self::EXPR_PREF . $CacheKey ) );
           
           echo '<h2>go recache</h2>';
           echo '<hr><pre>';
           var_export($carr);
           echo '<br>(('.$CacheKey;
           var_export($carr['dfltuser_5']);
           echo '<br>(('.self::EXPR_PREF . $CacheKey;
           var_export($carr['xpr_dfltuser_5']);
           echo '</pre><hr>';
           echo '<pre>';
           foreach($carr as $k => $v){
              echo "<br><b>$k</b> =>";
              //var_export($v);
              var_export($carr[$k]);
              
           }
           echo '</pre>';
           //exit;






        
        //if( false===( $c_arr = self::$memcache->get( Array( $CacheKey, self::EXPR_PREF . $CacheKey ) )) || !isset($c_arr[$CacheKey]) || !isset($c_arr[self::EXPR_PREF . $CacheKey]) ){
        if( false===( $cobj=self::$memcache->get($CacheKey) ) || false===( $expire=self::$memcache->get(self::EXPR_PREF . $CacheKey) ) ){

           echo '<h2>go recache</h2>';
           echo '<hr><pre>';
           var_export($c_arr);
           echo '<br>'.$CacheKey;
           var_export($c_arr[$CacheKey]);
           echo '<br>'.self::EXPR_PREF . $CacheKey;
           var_export($c_arr[self::EXPR_PREF . $CacheKey]);
           echo '</pre><hr>';
           echo '<pre>';
           foreach($c_arr as $k => $v){
              echo "<br><b>$k</b> =>";
              //var_export($v);
              var_export($c_arr[$k]);
              
           }
           echo '</pre>';
           
           return false;
        }

        //$cobj   = $c_arr[$CacheKey];
        //$expire = $c_arr[self::EXPR_PREF . $CacheKey];
        
        # Если время жизни кеша истекло, то перекешируем с условием блокировки
        if($expire < time()){
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
        
        echo '<hr>$cobj (in set): <pre>';
        var_export($cobj);
        echo '</pre><hr>';
        
        echo '<br><font color="red">set('.self::EXPR_PREF.$CacheKey.', '.$expire.', false, 0)</font>'.self::$memcache->set(self::EXPR_PREF.$CacheKey, $expire, false, 0);
        echo '<br><font color="red">set('.$CacheKey.', $cobj, self::COMPRES, 0)</font>'.self::$memcache->set($CacheKey, $cobj, self::COMPRES, 0);
        echo '<hr><font color="green"><pre>';
        var_export( self::$memcache->get( Array( $CacheKey, self::EXPR_PREF . $CacheKey ) ) );
        echo '</pre></font><hr>';        
        
        //self::$memcache->set(self::EXPR_PREF.$CacheKey, $expire, false, 0);
        //self::$memcache->set($CacheKey, $cobj, self::COMPRES, 0);
        
        

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
        return self::$memcache->delete($CacheKey);
    }
    
}

?>
