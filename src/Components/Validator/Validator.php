<?php
/**
 * Author: 沧澜
 * Date: 2019-10-14
 */

namespace Calject\LaravelProductivity\Components\Validator;


use CalJect\Productivity\Components\Criteria\Criteria;
use CalJect\Productivity\Exceptions\ClosureRunException;
use Calject\LaravelProductivity\Contracts\Validator\IConstraint;
use Calject\LaravelProductivity\Contracts\Validator\IValidate;
use CalJect\Productivity\Utils\SCkOpt;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator as LaravelValidator;
use Illuminate\Validation\ValidationException;

/**
 * Class Validator
 * @package Calject\LaravelProductivity\Components\Validator
 * 自定义laravel验证器拓展
 */
class Validator implements IValidate
{
    /*
    |--------------------------------------------------------------------------
    | 模式参数
    |--------------------------------------------------------------------------
    | OPT_V_VALIDATOR: 使用laravel验证器类验证，需要传入data数据
    | OPT_V_REQUEST:   使用Request对象验证方法验证，不需要传入data
    |
    | OPT_E_THROW_ERROR:    验证不通过强制抛出ValidationException异常
    | OPT_E_NO_THROW_ERROR: 验证不通过不抛出ValidationException异常(不作异常处理)
    |
    */
    const OPT_V_VALIDATOR = 1;
    const OPT_V_REQUEST = 1 << 1;
    
    const OPT_E_THROW_ERROR = 1 << 2;
    const OPT_E_NO_THROW_ERROR = 1 << 3;
    
    /**
     * @var IConstraint
     */
    protected $constraint;
    
    /**
     * @var array
     */
    protected $rules = [];
    
    /**
     * @var array
     */
    protected $withs = [];
    
    /**
     * @var Closure
     */
    protected $error;
    
    /**
     * @var int
     */
    protected $opts = 0;
    
    /**
     * Validator constructor.
     */
    public function __construct()
    {
        $this->_init();
    }
    
    /**
     * init
     */
    protected function _init()
    {
        $this->constraint = $this->_initConstraint();
    }
    
    /**
     * @return IConstraint
     */
    protected function _initConstraint(): IConstraint
    {
        return Constraint::getInstance();
    }
    
    /**
     * @param array|IValidate $data                                         待验证数据
     * @param Closure|null $validator function (array $data, $validates)    验证不通过回调处理
     * @return void
     * @throws ClosureRunException
     */
    public function validate($data = [], Closure $validator = null)
    {
        $validates = $this->withs + $this->constraint->getRules($this->rules);
        try {
            Criteria::opts($this->opts)->bind(self::OPT_V_REQUEST, function () use ($validates) {
                Request::capture()->validate($validates);
            })->default(function () use ($data, $validates, $validator) {
                if ($validator) {
                    call_user_func($validator, [$data, $validates]);
                } else {
                    LaravelValidator::make($data, $validates)->validate();
                }
            })->handle();
        } catch (ValidationException $exception) {
            $_isThrow = $this->error && call_user_func($this->error, $exception);
            /* ======== (opts未配置 == 0 || 没有设置回调或者回调返回true || opts & OPT_E_DISABLE == OPT_E_DISABLE) 则抛出异常 ======== */
            if ($this->opts === 0 || $_isThrow === true || SCkOpt::check($this->opts, self::OPT_E_NO_THROW_ERROR)) {
                throw $exception;
            }
        }
    }
    
    /**
     * @param array $rules laravel  Lang设置的验证规则数组
     * @param int $opts 配置参数
     * @return static
     */
    public static function make(array $rules, int $opts)
    {
        $validator = new static();
        $validator->rules = $rules;
        $validator->opts = $opts;
        return $validator;
    }
    
    /**
     * @param mixed ...$args 额外配置规则
     * @return $this
     */
    public function with(... $args)
    {
        $args = (func_num_args() == 1 && is_array($args[0])) ? $args[0] : $args;
        $this->withs = $args;
        return $this;
    }
    
    /**
     * 设置配置参数
     * @param int $opts
     * @return $this
     */
    public function opts(int $opts)
    {
        $this->opts = $opts;
        return $this;
    }
    
    /**
     * @param Closure $error
     * @return Validator
     */
    public function error(Closure $error)
    {
        $this->error = $error;
        return $this;
    }
    
}