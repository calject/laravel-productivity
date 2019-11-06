<?php
/**
 * Author: 沧澜
 * Date: 2019-10-14
 */

namespace Calject\LaravelProductivity\Components\Rules;

use Illuminate\Contracts\Validation\Rule;

/**
 * Class RuleIn
 * @package Calject\LaravelProductivity\Components\Rules
 */
class RuleIn implements Rule
{
    /**
     * 当前判断属性值
     * @var string
     */
    protected $attribute;
    
    /**
     * 是否严格判断
     * @var bool
     */
    protected $isStrict = true;
    
    /**
     * @var array
     */
    protected $inArr = [];
    
    /**
     * Create a new rule instance.
     */
    public function __construct()
    {
        //
    }
    
    /**
     * @param array $inArr
     * @param bool $isStrict
     * @return mixed
     */
    public static function in(array $inArr, $isStrict = true)
    {
        $instance = new static();
        return $instance->setInArr($inArr)->setIsStrict($isStrict);
    }
    
    
    /**
     * @param array $inArr
     * @return $this
     */
    public function setInArr(array $inArr)
    {
        $this->inArr = $inArr;
        return $this;
    }
    
    /**
     * @param bool $isStrict
     * @return $this
     */
    public function setIsStrict(bool $isStrict)
    {
        $this->isStrict = $isStrict;
        return $this;
    }
    
    /**
     * Determine if the validation rule passes.
     * @param string $attribute
     * @param mixed $value
     * @return bool
     */
    public function passes($attribute, $value)
    {
        $this->attribute = $attribute;
        return in_array($value, $this->inArr, $this->isStrict);
    }
    
    /**
     * Get the validation error message.
     * @return string
     */
    public function message()
    {
        return $this->attribute . ' 无效。';
    }
}
