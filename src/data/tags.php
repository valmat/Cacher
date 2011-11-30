<?php
   
   define('CACHER_TAG_REQUIRED',TRUE);


/**  Tags collection
  *  Теги кеширования
  *  Вынесение их в отдельную сущьность сделано для того, что бы значения кеширования устанавливлись прозрачно для программы,
  *  а управление ими не было распределно по разным частям кода.
  *
  *  В теге должны быть реализованы две абстрактные функции класса Cacher_Tag, т.е. должен быть реализован следующий интерфейс:
  *   class Cacher_Tag_Interface extends Cacher_Tag {
  *      static function setKey(ObjType $Obj){
  *        return KeyStringVal;
  *      }
  *      static function getBkName(){
  *        return CacheTypes::CACHETYPE;
  *      }
  *   }
  */
   
    /**
     *   Any simple tag
     * # CacheTypes::SAFE
     */
    class Cacher_Tag_SmplTag extends Cacher_Tag {
        static function getBkName(){
            return CacheTagTypes::MC;
        }
    }
    
    /**
     *   Any simple tag 1
     * # CacheTypes::SAFE
     */
    class Cacher_Tag_SmplTag1 extends Cacher_Tag {
        static function getBkName(){
            return CacheTagTypes::MC;
        }
    }
 

?>