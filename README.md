# Демо - обработка прайс листов поставщиков

Задача: импорт ценников от поставщиков. Количество поставщиков у каждого магазина 1-2 до 10. Общее количество аквтиных магазинов - около 1000. Каждый импортируемыей файл от 10МБ до 50Мб. Обновление один-два раза в день.
   
Постановка в очередь:  
 
```php 
// Отправляем задачу на обработку в очередь 
dispatch(new \App\Jobs\ImportPriceList(
    // путь к загруженному прайс-листу 
    new SplFileInfo(storage_path('app/goods/SOME_PRICE_16-02-2018_18-00_1.csv')),
    // код поставщика
    \App\Constants::SOME_PROVIDER_MOTORS,
    // ID пользовательского магазина (у одного пользователя может быть несколько магазинов) 
    $shop->id
));
```

## Работа с ActiveRecord 

Для каждого поставщика и магазина отдельная таблица В БД. Название формиурется по правилу: `goods_<provider>_<shop_id>`
Обновление происходит через временную таблицу: т.е. импорт товарных позиций просиходит в `goods_<provider>_<shop_id>_tmp`, по окочании импорта временная табица заменяет основную. 

> Напрямую AR создать нельзя (конструктор остается public, но при попытках запроса будет генерироваться исключение)

```php
// привычный алгоритм с AR работать не будет
$model = new App\Goods\Model\Goods();
$model->get(); // select * from `goods`, поскольку такой таблицы не существует, будет сгенерировано исключение

// прежде чем использовать AR Goods, необходимо точно знать какой магазин и какой поставщик
$model = \Goods::model( app()->make(App\Goods\TableOption, [App\Constants::BOSH_MOTORS, $shopID]) );
$model->get(); // select * from `goods_bosh_678`

// Получение объекта AR. Использует механизм Laravel Facede (класс App\Goods\GoodsFactory)
$model = Goods::model( /* опции */ );
// создать таблицу и вернуть объект AR
$model = Goods::create( /* опции */ );

/**
 * Отдельный класс TableOptions хранит параметры таблицы, и также генерирует название таблицы
 * которое может ипользоваться как в AR, так и при составлении запросов через QueryBuilder
 */ 

// создание через оператор new
$options = (new App\Goods\TableName())
    ->setProvider(App\Constants::BOSH_MOTORS)
    ->setShopID(666);
    
$options->getTableName(); // goods_bosh_666
     
// более короткая нотация через LaravelServiceContainer (класс App\Goods\GoodsServiceProvider)
$options = app()->make(App\Goods\TableOption, [App\Constants::BOSH_MOTORS, 666]);
$options->getTableName(); // goods_bosh_666

// известно название таблицы, необходимо получить параметры
$options = new App\Goods\TableOptions('goods_bosh_333');
$options->getProvider(); // bosh
$options->getShopID(); // 333

```

