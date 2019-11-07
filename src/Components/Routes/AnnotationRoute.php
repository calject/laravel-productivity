<?php
/**
 * Author: 沧澜
 * Date: 2019-11-05
 */

namespace Calject\LaravelProductivity\Components\Routes;


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
            $refClass = new ReflectionClass($className);
            if ($docComment = $refClass->getDocComment()) {
                /* ======== 匹配route ======== */
                if ($routeComment = self::matchTagContent($docComment, 'route')) {
                    $classParams = self::matchKeyValues($routeComment, self::matchValue($routeComment, [], function ($value) {
                        return ['prefix' => $value];
                    }));
                }
            }
            array_map(function (ReflectionMethod $refMethod) use (&$methodRoutes, $className) {
                if ($docComment = $refMethod->getDocComment()) {
                    if ($routeComment = self::matchTagContent($docComment, 'route')) {
                        $methodParams = self::matchKeyValues($routeComment, self::matchValue($routeComment, [], function ($value) {
                            return ['api' => $value];
                        }));
                    }
                    if ($methodParams = self::matchTagKeyValues($docComment, []) + ($methodParams ?? [])) {
                        $methodParams['action'] = ltrim(str_replace($this->namespace, '', $className).'@'.$refMethod->getName(), '\\');
                        $methodRoutes[] = $methodParams;
                    }
                
                }
            }, $refClass->getMethods());
            if ($methodRoutes) {
                $router = Route::namespace($this->namespace);
                if (isset($classParams)) {
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
        });
    }
    
    /**
     * @param string $docComment
     * @param string $tag
     * @param mixed $default
     * @return string|mixed
     */
    public static function matchTagContent(string $docComment, string $tag, $default = false)
    {
        if (preg_match('/\*[ ]*@' . $tag . '\((.*)\)\\n/', $docComment, $tagComment) && $tagComment[1]) {
            return $tagComment[1];
        } else {
            return $default;
        }
    }
    
    /**
     * @param string $docComment
     * @param string $tag
     * @param mixed $default
     * @return array|mixed
     */
    public static function matchTagTextContent(string $docComment, string $tag, $default = false)
    {
        if (preg_match('/\*[ ]*@' . $tag . "\('?([^'()]*)'?\)\n/", $docComment, $tagComment) && $tagComment[1]) {
            return $tagComment[1];
        } else {
            return $default;
        }
    }
    
    /**
     * @param string $docComment
     * @param mixed $default
     * @return array|mixed
     */
    public static function matchKeyValues(string $docComment, $default = false)
    {
        if (preg_match_all("/(\w*)='([^'()]*)'/", $docComment, $values) && $values[1]) {
            return array_combine($values[1], $values[2]);
        } else {
            return $default;
        }
    }
    
    /**
     * @param string $docComment
     * @param mixed $default
     * @return array|mixed
     */
    public static function matchTagKeyValues(string $docComment, $default = false)
    {
        if (preg_match_all("/\*[ ]*@(\w*)\('?([^'()]*)'?\)\n/", $docComment, $tagComment) && $tagComment[0]) {
            return array_combine($tagComment[1], $tagComment[2]);
        } else {
            return $default;
        }
    }
    
    /**
     * @param string $docComment
     * @param mixed $default
     * @param Closure $handle
     * @return bool|mixed
     */
    public static function matchValue(string $docComment, $default = false, Closure $handle = null)
    {
        if (preg_match("/'?([^'()]*)'?/", $docComment, $values) && $values[1]) {
            return $handle ? call_user_func($handle, $values[1]) : $values[1];
        } else {
            return $default;
        }
    }
}