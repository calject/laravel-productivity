<?php
/**
 * Author: 沧澜
 * Date: 2019-10-14
 */

namespace Calject\LaravelProductivity\Component\Rules;

use CalJect\Productivity\Contracts\Instance\Singleton\AbsSingleton;
use Illuminate\Support\Facades\Lang;

/**
 * Class Rules
 * @package Calject\LaravelProductivity\Component\Rules
 */
class Rules extends AbsSingleton
{
    /**
     * 定义的规则
     * @var array
     */
    protected $rules = [];
    
    /**
     * AbsSingleton constructor init handle.
     * @return mixed
     */
    protected function init()
    {
        $this->rules = Lang::get('validation.rules');
        $this->rules = is_array($this->rules) ? $this->rules : [];
    }
    
    /**
     * 获取路由规则构造
     * @param array ...$args
     * @return Rule
     */
    public static function get(... $args): Rule
    {
        if (isset($args[0]) && is_array($args[0])) {
            $args = $args[0];
        }
        $instance = self::getInstance();
        foreach ($args as $arg) {
            $base_rules[$arg] = $instance->rules[$arg] ?? 'required';
        }
        return new Rule($base_rules ?? []);
    }
    
}