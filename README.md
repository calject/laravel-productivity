# laravel-productivity


* [安装](#安装)
* [一、Components](#一components)
    * [1. Rules](#1rules)
    * [2. 注解路由实现](#2注解路由实现)
* [二、Commands](#二commands)
    * [1. 创建类顶部注释](#1caljectcommentdata)
    * [2. 创建数据库模型字段注释](#2caljectcommentmodel)
    * [3. 生成env映射配置文件](#3caljectconfigenv)
    * [4. 查看队列列表](#4caljectqueuelist)

## <span id="install">安装</span>

```composer
composer require calject/laravel-productivity
```

## v0.2.5

> 添加队列显示命令 [`calject:queue:list`](#caljectqueuelist)


## v0.2.*

> 添加路由注解实现`AnnotationRoute`

### <span id="components">一、Components</span>

#### <span id="Rules">1.`Rules`</span>

> 示例:
> 在`resources/lang/en`或者`resources/lang/en`(根据app.php配置)下定义键及验证规则

```php
    /*
     |--------------------------------------------------------------------------
     | 自定义字段验证规则
     |--------------------------------------------------------------------------
     | 自定义字段验证规则
     |
     */
    'rules' => [
        /* ======== 接口参数规则定义 ======== */
        'username' => 'required|string|min:2',
        'id_card' => 'required|alpha_num|min:15|max:18',
        'mobile' => 'required|digits:11',
        'bankcard' => 'required|digits_between:12,21',
        'sms_code' => 'required|digits_between:4,6',
        'unique_code' => 'required|string',
        'order_no' => 'required|string|max:32',
        'amount' => 'required|numeric',
        'periods' => 'required|numeric|min:1|max:12'
    ],

```

> 生成验证规则数组

```php
$rules = Rules::get(['username', 'id_card'])->rules();

/** 生成结果:
 array:4 [▼
   "username" => "required|string|min:2"
   "id_card" => "required|alpha_num|min:15|max:18"
   "bankcard" => "required|digits_between:12,21"
   "mobile" => "required|digits:11"
 ]
*/

$rules = \App\Repositories\Component\Rules\Rules::get(['username', 'id_card', 'bankcard', 'mobile'])->with([
    'value1' => 'required|string',
    'value2' => 'json'
])->rules();

/** 生成结果:
 array:6 [▼
   "value1" => "required|string"
   "value2" => "json"
   "username" => "required|string|min:2"
   "id_card" => "required|alpha_num|min:15|max:18"
   "bankcard" => "required|digits_between:12,21"
   "mobile" => "required|digits:11"
 ]
*/

$rules = Rules::get(['ssss', 'uuuu'])->with([
    'value1' => 'required|string',
    'value2' => 'json'
])->rules();

/** 生成结果:
 array:4 [▼
   "value1" => "required|string"
   "value2" => "json"
   "ssss" => "required"
   "uuuu" => "required"
 ]
*/

```

### <span id="annotationRoute">2.注解路由实现</span>

> `AnnotationRouteLocalProvider`、`AnnotationRouteProvider`、`AnnotationRoute`

#### 服务提供者注册实现
* `config/app.php` => 'providers' 属性中添加`AnnotationRouteLocalProvider`或`AnnotationRouteProvider`服务提供者

> `AnnotationRouteLocalProvider`仅在env环境为local中生效, `AnnotationRouteProvider` 在所有环境中生效, 可通过`AnnotationRoute`的`env`方法设置

#### 自定义实现
* `app/Providers/RouteServiceProvider.php` 中添加注解实现`AnnotationRoute`

```php
    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapApiRoutes();

        $this->mapCreditRoutes();

        $this->mapDevelopRoutes();
        
        $this->mapTestRoutes();
        
        // 添加注解实现
        $annotationRoute = new AnnotationRoute();
        // $annotationRoute->envs('local');    // 设置生效环境
        // $annotationRoute->envs(['local', 'develop']);    // 设置生效环境
        $annotationRoute->mapRefRoutes();
    
    }
```

#### 使用

* Class顶部注释(可选)
    * @route()
        * prefix(string  $prefix)
            * `prefix='test'`
        * middleware(string $middleware)
            * `middleware='api'`、`middleware='api,mid,mid2'`
    * 示例
        * `@route(prefix='test', middleware='api,mid,mid2')`
        
* Method 可选注释
    * @api(string $path)
        * `@api('test/TestA')`、`@api(test/TestA)`
    * @method(string $method)
        * `@method('get')`、`@method('get,post,put,delete')`
    * @middleware(string $middleware)
        * `@middleware('test')`、`@middleware('test,mid,mid2')`
    * @name(string $value)
        * `@name('TestControllerTestA')`、 `@name(TestControllerTestA)`
    * @prefix(string  $prefix)
        * `@prefix('test')`、 `@prefix(test)`

* 示例        

```php
<?php
namespace App\Http\Controllers\Test;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

/**
 * Class TestRouterController
 * @package App\Http\Controllers\Test
 * @route(prefix='test', middleware='api,mid,mid2')
 */
class TestController extends Controller 
{
    /**
     * @param Request $request
     * @return ResponseFactory|Response
     * @api('testA')
     * @name(testA)
     */
    public function testA(Request $request)
    {
        return response('testA');
    }
    
    /**
     * @param Request $request 
     * @return ResponseFactory|Response 
     * @route(api='testD', method='get,post,put', name='testD')
     * @middleware(testD)
     */
    public function testD(Request $request)
    {
        return response('testD');
    }    
}
```

* 执行`php artisan route:list`查看路由列表

```
+--------+----------------------------------------+-----------------------------+-------+---------------------------------------------------------------------+---------------+
| Domain | Method                                 | URI                         | Name  | Action                                                              | Middleware    |
+--------+----------------------------------------+-----------------------------+-------+---------------------------------------------------------------------+---------------+
|        | GET|HEAD|POST|PUT|PATCH|DELETE|OPTIONS | test/testA                  | testA | App\Http\Controllers\Test\TestRouterController@testA                | api,mid       |
|        | GET|POST|PUT|HEAD                      | test/testD                  | testD | App\Http\Controllers\Test\TestRouterController@testD                | api,mid,testD |
+--------+----------------------------------------+-----------------------------+-------+---------------------------------------------------------------------+---------------+
```


### <span id="commands">二、Commands</span>

#### <span id="commant-data">1.`calject:comment:data`</span>

* 创建类属性顶部(get/set/apt/property)注释

```
Description:
  根据类属性生成类(get/set/apt/property)属性注释

Usage:
  calject:comment:data [options] [--] <path>

Arguments:
  path                       执行目录或文件

Options:
      --def-cur              生成当前类默认注释[--get/--set](未传入参数默认为该配置项)
      --def                  使用默认配置生成注释[--get/--set/--cur],使用--no-xxx取消
      --all                  应用所有配置[--get/--set/--pro/--apt],使用--no-xxx取消
      --get                  生成get方法注释
      --set                  生成set方法注释
      --pro                  生成property属性注释
      --apt                  生成adapter方法注释
      --cur                  仅生成当前class属性对应方法
      --no-get               不生成get方法注释
      --no-set               不生成set方法注释
      --no-pro               不生成property属性注释
      --no-apt               不生成adapter方法注释
      --no-cur               生成所有属性,包含继承
      --def-var[=DEF-VAR]    设置默认位置属性值 [default: "mixed"]
      --var-tag[=VAR-TAG]    设置默认检查的属性值 [default: "var"]
      --note-tag[=NOTE-TAG]  设置默认检查文本值 [default: "note"]
```

* 生成示例

```php
/**
 * Class AnnotationRoute
 * @package Calject\LaravelProductivity\Components\Routes
 * ---------- set ----------
 * @method $this setEnvs($envs)                  生效环境
 * @method $this setControllers($controllers)    注解查询的路径[相对路径]
 * 
 * ---------- get ----------
 * @method mixed getEnvs()           生效环境
 * @method mixed getControllers()    注解查询的路径[相对路径]
 * 
 * ---------- apt ----------
 * @method $this|mixed envs($envs = null)                  生效环境
 * @method $this|mixed controllers($controllers = null)    注解查询的路径[相对路径]
 */
class AnnotationRoute extends CallDataProperty
{
    use TCallDataPropertyByName;
    /**
     * @var string
     */
    private $namespace = 'App\Http\Controllers';
    
    /**
     * @note 生效环境
     * @var mixed
     * @explain array|string 传入数组或者字符 默认为所有环境生效
     * @example local 、 produce 、 ['local', 'develop'] 、 ...
     */
    protected $envs;
    
    /**
     * @note 注解查询的路径(相对路径)
     * @var mixed
     * @explain array|string 传入数组或者字符 默认为空查询app/Http/Controllers下所有控制器文件
     * @example Test 、 User 、['Test'、 'User'] 、 ...
     */
    protected $controllers;
    
}
```

#### <span id="commant-model">2.`calject:comment:model`</span>

* 创建数据库模型类属性注释

```
Description:
  根据模型库表连接生成表注释

Usage:
  calject:comment:model [<dir>]

Arguments:
  dir                   执行目录,默认为app/Models
```

* 创建示例

```mysql
CREATE TABLE `test` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT COMMENT 'id',
  `order_no` varchar(50) DEFAULT NULL COMMENT '订单',
  `amount` int(11) unsigned NOT NULL COMMENT '金额',
  `channel_id` tinyint(2) unsigned NOT NULL COMMENT '渠道',
  `status` tinyint(2) unsigned DEFAULT '0' COMMENT '状态: 1.xxx 2.xxx',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `deleted_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `order_no` (`order_no`),
);
```

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Class TestModel
 * @property int    $id                   id
 * @property string $order_no             订单
 * @property int    $amount               金额
 * @property int    $channel_id           渠道
 * @property int    $status               状态: 1.xxx 2.xxx
 * @property string $created_at           
 * @property string $updated_at           
 * @property string $deleted_at           
 * @package App\Models
*/
class TestModel extends Model
{
    protected $table = 'test';
}
```

#### <span id="config-env">3.`calject:config:env`</span>

* 根据`.env`文件生成`config/env.php`文件

* 生成示例

```php
<?php

return [
	'APP_NAME' => env('APP_NAME'),
	'APP_ENV' => env('APP_ENV'),
	'APP_KEY' => env('APP_KEY'),
	'APP_DEBUG' => env('APP_DEBUG'),
	'APP_URL' => env('APP_URL'),
	'RECEIVE_URL' => env('RECEIVE_URL'),
	'LOG_CHANNEL' => env('LOG_CHANNEL'),
	'DB_CONNECTION' => env('DB_CONNECTION'),
	'DB_HOST' => env('DB_HOST'),
	'DB_PORT' => env('DB_PORT'),
	'DB_USERNAME' => env('DB_USERNAME'),
	'DB_PASSWORD' => env('DB_PASSWORD'),
	'DB_PREFIX' => env('DB_PREFIX'),
	'DB_DATABASE_PAYMENT' => env('DB_DATABASE_PAYMENT'),
	'DB_DATABASE_PUBLIC' => env('DB_DATABASE_PUBLIC'),
	'BROADCAST_DRIVER' => env('BROADCAST_DRIVER'),
	'CACHE_DRIVER' => env('CACHE_DRIVER'),
	'SESSION_DRIVER' => env('SESSION_DRIVER'),
	'QUEUE_DRIVER' => env('QUEUE_DRIVER'),
	'ELASTIC_HOST' => env('ELASTIC_HOST'),
	'ELASTIC_LOG_INDEX' => env('ELASTIC_LOG_INDEX'),
	'ELASTIC_LOG_TYPE' => env('ELASTIC_LOG_TYPE'),
	'REDIS_HOST' => env('REDIS_HOST'),
	'REDIS_PASSWORD' => env('REDIS_PASSWORD'),
	'REDIS_PORT' => env('REDIS_PORT'),
	'MAIL_DRIVER' => env('MAIL_DRIVER'),
	'MAIL_HOST' => env('MAIL_HOST'),
	'MAIL_PORT' => env('MAIL_PORT'),
	'MAIL_USERNAME' => env('MAIL_USERNAME'),
	'MAIL_PASSWORD' => env('MAIL_PASSWORD'),
	'MAIL_ENCRYPTION' => env('MAIL_ENCRYPTION'),
	'PUSHER_APP_ID' => env('PUSHER_APP_ID'),
	'PUSHER_APP_KEY' => env('PUSHER_APP_KEY'),
	'PUSHER_APP_SECRET' => env('PUSHER_APP_SECRET'),
	'PUSHER_APP_CLUSTER' => env('PUSHER_APP_CLUSTER'),
	'MIX_PUSHER_APP_KEY' => env('MIX_PUSHER_APP_KEY'),
	'MIX_PUSHER_APP_CLUSTER' => env('MIX_PUSHER_APP_CLUSTER'),
];
```

#### <span id="expand">4.`calject:queue:list`</span>

* 显示队列关系列表

```
Description:
  查询匹配所有已定义队列

Usage:
  calject:queue:list
```
* 示例

```
+-------+-------------------+-----------------------------------------------------------+
| queue | class             | path                                                      |
+-------+-------------------+-----------------------------------------------------------+
| call  | App\Jobs\TestJob  | /Users/kaka/sites/calject/la-server/app/Jobs/TestJob.php  |
| back  | App\Jobs\TestJob2 | /Users/kaka/sites/calject/la-server/app/Jobs/TestJob2.php |
|       | App\Jobs\TestJob3 | /Users/kaka/sites/calject/la-server/app/Jobs/TestJob3.php |
|       | App\Jobs\TestJob4 | /Users/kaka/sites/calject/la-server/app/Jobs/TestJob4.php |
+-------+-------------------+-----------------------------------------------------------+

```


