<?php
/**
 * Author: 沧澜
 * Date: 2019-10-14
 */

namespace Calject\LaravelProductivity\Components\Rules;

/**
 * Class Rule
 * @package Calject\LaravelProductivity\Components\Rules
 */
class Rule
{
    /**
     * 基础验证规则
     * @var array
     */
    protected $baseRules = [];
    
    /**
     * 附加验证规则
     * @var array
     */
    protected $expandRules = [];
    
    /**
     * Rule constructor.
     * @param $rules
     */
    public function __construct(array $rules)
    {
        $this->baseRules = $rules;
    }
    
    /**
     * 追加基础校验参数
     * @param array ...$args
     * @return $this
     */
    public function appendBase(... $args)
    {
        if (is_array($args[0])) {
            $args = $args[0];
        }
        $this->baseRules = $args + $this->baseRules;
        return $this;
    }
    
    /**
     * 追加额外校验参数
     * @param array ...$args
     * @return $this
     */
    public function appendExpand(... $args)
    {
        if (is_array($args[0])) {
            $args = $args[0];
        }
        $this->expandRules = $args + $this->expandRules;
        return $this;
    }
    
    /**
     * @param array $expandRules
     * @return $this
     */
    public function with(array $expandRules)
    {
        $this->expandRules = $expandRules;
        return $this;
    }
    
    /**
     * @return array
     */
    public function rules(): array
    {
        return $this->expandRules + $this->baseRules;
    }
    
}