<?php
################################################################################
/**
  *  [Cacher & Cacher_Tag]
  *  config for class  Cacher and Cacher_Tag
  */


    class CONFIG_Cacher {
        # NameSpase prefix for cache key
        const NAME_SPACE    = 'dflt';
        
        # NameSpase prefix for tag key
        const TAG_NM_SPACE  = 'dflt_k';
        
        const PATH_TAGS     = './src/data/tags.php';
        const PATH_SLOTS    = './src/data/slots.php';
        const PATH_BACKENDS = './src/data/strategy/';
        
        
       
    }
    
    /***************************************************************************
     * Пронстранство имен типов кеширования. Создано в такой упрощенной форме, для экономии системных ресурсов
     * Цель: структуризация типов используемых кешей.
     * Т.е. общий замысел такой: Конкретный слот указывает не на непосредственно конкретный бэкенд, а на константу из пространства имен CacheTypes
     * исходя только из постановки задачи и области применения. В этом случае изменение стратегии кеширования - это не переписывание каждого слота,
     * а перестройка констант в namespase CacheTypes
     */
    
    class CacheTypes{
        
        /**
          * Быстрый но дорогой кеш (в памяти). Низкая надежность.
          * Следует применять для часто запроашиваемых данных с относительно дешевым способом получения, позволяющих дорогое хранилище
          */
        const SIMPLEST = 'MemCache';
        
        /**
          * Быстрый но дорогой кеш (в памяти). Низкая надежность.
          * Следует применять для часто запроашиваемых данных с относительно дешевым способом получения, позволяющих дорогое хранилище
          * При перестроении кеша выдаются протухшие данные до тех пор, пока новые данные не будут получены.
          * Состояние гонки и одновременный запрос на перестроение кеша исключены
          */
        //const FAST = 'MemReCache';
        const FAST = 'notag_Memcache';
        
    
        /**
          * Достаточно быстрое (в основном в памяти), и в тоже время надежное хранилище.
          * Надежностиь достигается за счет каскадности. Т.е. Кеширование происходит на двух уровнях в быстрое (память) и надежное (файлы) хранилища.
          * Поскольку перекешированием занимается только один процесс (за счет блокировок) этот способ не очень сильно нагружает сервер
          * Следует применять для данных, получаемых тяжелым путем (тяжелые запросы и т.п.) и позволяющих использование дорогого хранилища
          */
        const SAFE = 'MemReFile';
    
        /**
          * Дешевый (в основном на файлах) способ кеширования, но менее производительный по сравнению с self::FAST
          * Следует применять для данных, требующих надежное хранилище
          * или данных, для который использование более быстрых способов в пределах текущей конфигурации сервера непозволительно дорого.
          * (В случае переконфигурирования сервера можно сделать self::CHEAP = self::SAFE)
          */
        const CHEAP = 'MemReFile';        

        /**
          * Быстрый но дорогой кеш (в памяти). Низкая надежность.
          * Следует применять для часто запроашиваемых данных с относительно дешевым способом получения, позволяющих дорогое хранилище
          */
        const NOTAG_SIMPLEST = 'notag_MemCache';
        
        /**
          * Быстрый но дорогой кеш (в памяти). Низкая надежность. С перекешированием
          * БЫСТРОЕ перекеширование ПРИ УДАЛЕНИИ.
          * Следует применять для часто запроашиваемых данных с относительно дешевым способом получения, позволяющих дорогое хранилище
          * При перестроении кеша выдаются протухшие данные до тех пор, пока новые данные не будут получены.
          * Состояние гонки и одновременный запрос на перестроение кеша исключены
          */
        const NOTAG_FAST_ONDEL = 'notag_MemReCache';
        
        /**
          * Быстрый но дорогой кеш (в памяти). Низкая надежность. С перекешированием
          * БЫСТРОЕ перекеширование ПРИ ИСТЕЧЕНИИ времени жизни.
          * Следует применять для часто запроашиваемых данных с относительно дешевым способом получения, позволяющих дорогое хранилище
          * При перестроении кеша выдаются протухшие данные до тех пор, пока новые данные не будут получены.
          * Состояние гонки и одновременный запрос на перестроение кеша исключены
          */
        const NOTAG_FAST_EXPIR = 'notag_MemReCache0';
        
    }
    
    class CacheTagTypes{
        
        /**
          * Быстрое хранилище (в памяти). Низкая надежность.
          * Стабильная работа expire
          */
        const FAST = 'Memcache';// <--- Неправильно!
    
        /**
          * Без тегов. Использовать там, где теги не нужны
          */
        const NOTAG = 'empty';
    }

?>