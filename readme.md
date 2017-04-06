## Установка

1 . Установить через composer
```
$ composer require sitesoft/laravel-apis
```

2 . В файле `config/app.php` добавить в `providers`
```
Sitesoft\LaravelApis\ApisServiceProvider::class
```

3 . Чтобы разрешить CORS для всех api запросов, добавьте `HandleCors` middleware в `$middlewareGroups` в файле `app/Http/Kernel.php`:
([См. раздел "Поддержка Cross-Origin Resource Sharing (CORS)"](#%D0%9F%D0%BE%D0%B4%D0%B4%D0%B5%D1%80%D0%B6%D0%BA%D0%B0-cross-origin-resource-sharing-cors))
```
protected $middlewareGroups = [
    'web' => [
       // ...
    ],

    'api' => [
        // ...
        \Barryvdh\Cors\HandleCors::class,
    ],
];
```

4 . Опубликовать файл настроек `apis.php` из пакета:
```
$ php artisan vendor:publish --provider="Sitesoft\LaravelApis\ApisServiceProvider"
```

## Использование

#### Добавление нового проекта на Sitesoft APIS
```
$ php artisan apis:create-project <name> <version> <url> --path=<path>
```
где
- `<name>` - имя нового проекта (можно использовать кириллицу)
- `<version>` - версия апи (только цифры)
- `<url>` - базовый url к апи (должен начинаться с `http` или `https`, например `http://test.com/api`)
- `<path>` - путь к api директории относительно папки `app/Http/Controllers/` (не обязательно, по умолчанию `Api`)

например, если файлы вашего api находятся в папке `app/Http/Controllers/Api/v1` то команда будет следующей:
```
$ php artisan apis:create-project MyApi 1 http://test.com/api --path="Api/v1"
```

эта команда сделает следующее:
1. сгенерирует токен и запишет в файл `config/apis.php`
2. добавит `SwaggerController.php` в `app/Http/Controllers/Api/v1/`
3. добавит маршрут для swagger'а в файл `routes/api.php`
4. сделает запрос к Sitesoft APIS на добавление нового проекта
5. добавит в файл настроек `apis.php` путь для генерации `swagger.json`

#### Генерация `swagger.json`
```
$ php artisan swaggen
```
Папки для генерации задаются в файле настроек `config/apis.php` в параметре `paths`.
Swagger просканирует эти папки и создаст в каждой свой `swagger.json`.

#### Поддержка Cross-Origin Resource Sharing (CORS)

Ваши api методы должны возвращать заговолок `Access-Control-Allow-Origin` для того, чтобы иметь возможность просмотреть ответ прямо на Sitesoft APIS.

Для поддержки CORS используется [barryvdh/laravel-cors](https://packagist.org/packages/barryvdh/laravel-cors), для его работы достаточно выполнить пункт 3 из раздела [Установка](#%D0%A3%D1%81%D1%82%D0%B0%D0%BD%D0%BE%D0%B2%D0%BA%D0%B0).
Или вы можете включить CORS только для некоторых маршрутов:

```
Route::group(['middleware' => 'cors'], function(){
    Route::get('/create', 'Api\CreateController@index');
    Route::post('/add', 'Api\AddController@index');
    Route::delete('/delete', 'Api\DeleteController@index');
});
```
или так
```
Route::get('/create', 'Api\CreateController@index')->middleware('cors');
Route::post('/add', 'Api\AddController@index')->middleware('cors);
Route::delete('/delete', 'Api\DeleteController@index')->middleware('cors');
```
У модуля laravel-cors также есть возможность настройки https://github.com/barryvdh/laravel-cors#configuration

#### Добавление новой версии на Sitesoft APIS
```
$ php artisan apis:add-version <version> <url> --path=<path>
```
где
- `<version>` - версия апи (только цифры)
- `<url>` - базовый url к апи (должен начинаться с `http` или `https`, например `http://test.com/api/v2`)
- `<path>` - путь к api директории относительно папки `app/Http/Controllers/` (не обязательно, по умолчанию `Api_v<version>`)

например, если ваши файлы с новой версией api находятся в папке `app/Http/Controllers/Api/v2` (относительно корня проекта) то команда будет следующей:
```
$ php artisan apis:add-version 2 http://test.com/api/v2 --path="Api/v2"
```
эта команда сделает следующее:
1. добавит `SwaggerController.php` в `app/Http/Controllers/Api/v2/`
2. добавит маршрут для swagger'а в файл `routes/api.php`
3. сделает запрос к Sitesoft APIS на добавление новой версии проекта
4. добавит в файл настроек `apis.php` путь для генерации `swagger.json`
