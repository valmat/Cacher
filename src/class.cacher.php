<?php

  /**
    * class Cacher
    * Требует наличия классов унаследованных от Cacher_Backend - семейство классов, реализующих бэкэнд для класса Cacher
    * Соответственно Cacher реализует фабричные методы для работы с бэкэндом.
    * Бэкэндом может быть файловая система, shared memory, memcache, Sqlite и другие системы кеширования
    * Для работы с классом представляются слоты и теги. Слоты реализованы в виде набора дружественных функций и неявно зашиты  в интерфейс текущего класса
    *
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

final class Cacher {
    
    const PATH_SLOTS    = CONFIG_Cacher::PATH_SLOTS;
    const PATH_BACKENDS = CONFIG_Cacher::PATH_BACKENDS;
    
    /**
     *  NameSpase prefix for cache key
     *  var set as default should be redefined
     *  @var string
     */
    const  NAME_SPACE   = CONFIG_Cacher::NAME_SPACE;
    
    /**
     *  Backend object responsible for this cache slot.
     *  @var Cacher_Backend
     */
    private    $Backend;
    /**
     * Lifetime of this slot.
     * @var int
     */
    private    $LifeTime;
    /**
     * Tags attached to this slot.
     * @var array of Cacher_Tag
     */
    private    $Tags = Array();
    
    /*
     * private constructor
     */
    private function __construct() {}
    
    /*
     * Выполняет подключение слота кеширования $SlotName и передает ему необходимые для создания аргументы $arg
     * Подключение выполняется с помощью функций - друзей класса Cacher
     * Init new Cacher Slot
     * function Slot
     * @param $SlotName
     * @param $arg
     */
    static function create($SlotName, $arg) {
      if (!defined('CACHER_SLOT_REQUIRED'))
        require self::PATH_SLOTS;
        
      $SlotName = 'Cacher_Slot_'.$SlotName;
      $Options = $SlotName($arg);
      
      $SelfObj = new Cacher();
      $SelfObj->LifeTime    = $Options[1];
      $SelfObj->Backend     = self::setBackend($Options[0]/*BackendName*/, $Options[2]/*CacheKey*/);
      return $SelfObj;
    }
    
    /*
     * function setOption
     * Этот метод создан для использования в в Слоте
     * 
     * @param $Backend Cacher_Backend
     * @param $LifeTime int
     * @param $key string
     */
    static function setOption($BackendName, $LifeTime, $key) {
        return Array($BackendName, $LifeTime, $key);
    }
  
    /**
     * Добавляет тег к слоту
     * 
     * @param Cacher_Tag $tag   Tag object to associate.
     * @return void
     */
    public function addTag(Cacher_Tag $tag) {
        if ($tag->getBkName() == $this->Backend->tagsType()) {
            $this->Tags[] = $tag->getKey();
            return true;
        }
        trigger_error('Backends for tag ' . get_class($tag) . ' and slot ' . get_class($this) . ' must be same', E_USER_WARNING);
        return false;
    }
    
    /*
     * Фабричный метод создания бэкэнда
     * Возвращает объект бэкенда по его имени. Попутно проверяет доступно ли использовать данное имя.
     * Лучше всего использовать этот метод, чем создавать объект быкенда на прямую.
     * function setBackend
     * @param $BackendName string
     * @param $CacheKey string
     */
    static function setBackend($BackendName, $CacheKey) {
        require_once self::PATH_BACKENDS .'slotbk/'. strtolower($BackendName) . '.php';
        $BackendName = 'Cacher_Backend_'.$BackendName;
        return new $BackendName($CacheKey, self::NAME_SPACE);
    }
    
    /*
     * Get a data of this slot. If nothing is found, returns false.
     * Получить данные из кеша
     * function get
     * @param void
     * @return mixed   Complex data or false if no cache entry is found.
     */
    public function get() {
        return $this->Backend->get();
    }
    
    /*
     * Установить кешу значение $val
     * Saves a data for this slot. 
     * 
     * function set
     * @param mixed $val  Data to be saved.
     * @return bool - успешность операции
     */
    public function set($val) {
        return $this->Backend->set($val, $this->Tags, $this->LifeTime);
    }

    /*
     * Removes a data of specified slot.
     * Удалить кеш
     * function del
     * @param void
     * @return void
     */
    public function del() {
        $this->Backend->del();
    }
  
}

?>