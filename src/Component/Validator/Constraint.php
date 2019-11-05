<?php
/**
 * Author: 沧澜
 * Date: 2019-10-14
 */

namespace Calject\LaravelProductivity\Component\Validator;

use CalJect\Productivity\Contracts\Instance\Singleton\AbsSingleton;
use Calject\LaravelProductivity\Contracts\Validator\IConstraint;
use Illuminate\Support\Facades\Lang;

/**
 * Class Constraint
 * @package Calject\LaravelProductivity\Component\Validator
 * laravel Lang 验证规则数组类
 */
final class Constraint extends AbsSingleton implements IConstraint
{
    /**
     * @var array
     */
    protected $rules = [];
    
    /**
     * AbsSingleton constructor init handle.
     * @return mixed
     */
    protected function init()
    {
        $this->rules = (array)Lang::get('validation.rules');
    }
    
    /**
     * @return array
     */
    public static function rules(): array
    {
        return self::getInstance()->rules;
    }
    
    /**
     * @param mixed ...$args
     * @return array
     */
    public function getRules(... $args): array
    {
        $args = (func_num_args() == 1 && is_array($args[0])) ? $args[0] : $args;
        foreach ($args as $arg) {
            $rules[$arg] = self::rules()[$arg] ?? 'required';
        }
        return $rules ?? [];
    }
    
    /**
     * @return array
     */
    public function getAllRules(): array
    {
        return $this->rules;
    }
    
}