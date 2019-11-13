<?php
/**
 * Author: 沧澜
 * Date: 2019-11-05
 */

namespace Calject\LaravelProductivity\Components\Routes;


use CalJect\Productivity\Components\Annotations\AnnotationTag;
use CalJect\Productivity\Components\DataProperty\CallDataProperty;
use CalJect\Productivity\Contracts\DataProperty\TCallDataPropertyByName;
use CalJect\Productivity\Utils\GeneratorFileLoad;
use Closure;
use Illuminate\Support\Facades\Route;
use ReflectionClass;
use ReflectionMethod;

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
    
    /**
     * @explain 遍历控制器文件
     */
    public function mapRefRoutes()
    {
        if ($this->envs && !in_array(app('env'), (array)$this->envs)) {
            return;
        }
        if ($this->controllers) {
            array_map(function ($path) {
                $this->registerRoutes($path);
            }, (array)$this->controllers);
        } else {
            $this->registerRoutes(app_path('Http/Controllers'));
        }
    }
    
    /**
     * @param $controllerPath
     */
    protected function registerRoutes($controllerPath)
    {
        (new GeneratorFileLoad($controllerPath))->eachFiles(function ($filePath) use ($controllerPath) {
            $className = rtrim(str_replace('/', '\\', str_replace($controllerPath, $this->namespace, $filePath)), '.php');
            if (!class_exists($className) || !is_subclass_of($className, 'App\Http\Controllers\Controller')) {
                goto end;
            }
            /* ======== 解析路由注解 ======== */
            $refClass = new ReflectionClass($className);
            $classParams = $this->matchTagContent($refClass->getDocComment(), 'prefix', []);
            array_map(function (ReflectionMethod $refMethod) use (&$methodRoutes, $className) {
                if ($methodParams = $this->matchTagContent($refMethod->getDocComment(), 'api')) {
                    $methodParams['action'] = ltrim(str_replace($this->namespace, '', $className).'@'.$refMethod->getName(), '\\');
                    $methodRoutes[] = $methodParams;
                }
            }, $refClass->getMethods());
            /* ======== 注册路由 ======== */
            if ($methodRoutes) {
                $router = Route::namespace($this->namespace);
                if ($classParams) {
                    foreach ($classParams as $property => $values) {
                        $router->{$property}($values);
                    }
                }
                $router->group(function () use ($methodRoutes){
                    foreach ($methodRoutes as $key => $methodRoute) {
                        if (!isset($methodRoute['api'])) {
                            continue;
                        }
                        $method = explode(',', $methodRoute['method'] ?? 'any');
                        if (count($method) > 1) {
                            $route = Route::match($method, $methodRoute['api'], $methodRoute['action']);
                        } else {
                            $route = Route::{$method[0] ?? 'any'}($methodRoute['api'], $methodRoute['action']);
                        }
                        unset($methodRoute['action'], $methodRoute['api'], $methodRoute['method']);
                        foreach ($methodRoute as $routeKey => $routeValue) {
                            $route->{$routeKey}($routeValue);
                        }
                    }
                });
            }
            end:
        });
    }
    
    /**
     * 匹配所有注解相关[@tag(...)]内容并合并
     * @param string $docComment    doc comment
     * @param string $key           content key
     * @param mixed|null $default   default
     * @return array|mixed|null
     */
    protected function matchTagContent($docComment, string $key, $default = null)
    {
        if ($docComment) {
            $tags = AnnotationTag::matchTagKeyValues($docComment, []) + $this->matchRouteContent($docComment, $this->defWithArrValue($key), []);
            unset($tags['route']);
            return $tags;
        } else {
            return $default;
        }
    }
    
    /**
     * 匹配route注解内容
     * @param string $docComment    doc comment
     * @param Closure $doDefault    function($value) {}
     * @param mixed|null $default   default
     * @return array|null
     */
    protected function matchRouteContent($docComment, Closure $doDefault, $default = null)
    {
        if ($routeComment = AnnotationTag::matchTagContent($docComment, 'route')) {
            return AnnotationTag::matchKeyValues($routeComment, AnnotationTag::matchValue($routeComment, [], $doDefault));
        } else {
            return $default;
        }
    }
    
    /**
     * @param string $keyName
     * @return Closure
     */
    protected function defWithArrValue(string $keyName)
    {
        return function ($value) use ($keyName) {
            return [$keyName => $value];
        };
    }
}