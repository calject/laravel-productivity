<?php
/**
 * Author: 沧澜
 * Date: 2019-11-11
 */

namespace Calject\LaravelProductivity\Components\DataProperty;

use Calject\LaravelProductivity\Components\Rules\Rules;
use CalJect\Productivity\Components\Annotations\AnnotationTag;
use CalJect\Productivity\Components\DataProperty\CallDataProperty;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use ReflectionClass;
use ReflectionException;
use ReflectionProperty;

/**
 * Class RequestCallDataProperty
 * @package Calject\LaravelProductivity\Components\DataProperty
 */
class RequestCallDataProperty extends CallDataProperty
{
    /**
     * @var array
     */
    private static $caches = [];
    
    /**
     * RequestCallDataProperty constructor.
     * @throws ReflectionException
     */
    public function __construct()
    {
        if (!($caches = $this->caches())) {
            $refClass = new ReflectionClass($this);
            array_map(function (ReflectionProperty $property) use (&$rules, &$maps) {
                $maps[$property->getName()] = $property->getName();
                if ($docComment = $property->getDocComment()) {
                    $rule = AnnotationTag::matchTagContentToArray($docComment, 'rule');
                    $values = AnnotationTag::matchTagKeyValues($docComment);
                    if ($rule || $values) {
                        $name = $values['name'] ?? $rule['name'] ?? $property->getName();
                        $rules[$name] = $values['rule'] ?? $rule['rule'] ?? 'required';
                        $maps[$property->getName()] = $name;
                    }
                }
            }, $refClass->getProperties());
            $rules = $rules ? Rules::get(array_keys($rules))->with($rules)->rules() : $rules;
            $this->setCache(['rules' => $rules, 'maps' => $maps]);
        }
        if ($maps = $this->cache('maps')) {
            $request = Request::capture();
            foreach ($maps as $propertyName => $requestName) {
                $this->{$propertyName} = $request->input($requestName);
            }
        }
    }
    
    /**
     * 数据验证
     * @throws ValidationException
     */
    public function validate()
    {
        Request::capture()->validate($this->cache('rules', []));
    }
    
    
    /**
     * @param array $cache
     * @return $this
     */
    final protected function setCache(array $cache)
    {
        self::$caches[static::class] = $cache;
        return $this;
    }
    
    /**
     * @param string $key
     * @param null $default
     * @return mixed|null
     */
    final protected function cache(string $key = null, $default = null)
    {
        $cache = $this->caches();
        if ($key && $cache) {
            return $cache[$key] ?? $default;
        } else {
            return $cache;
        }
    }
    
    /**
     * @return mixed|null
     */
    final protected function caches()
    {
        return self::$caches[static::class] ?? null;
    }
    
}