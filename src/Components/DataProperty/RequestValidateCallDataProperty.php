<?php
/**
 * Author: 沧澜
 * Date: 2019-11-11
 */

namespace Calject\LaravelProductivity\Components\DataProperty;

use ReflectionException;

/**
 * Class RequestValidateCallDataProperty
 * @package Calject\LaravelProductivity\Components\DataProperty
 */
class RequestValidateCallDataProperty extends RequestCallDataProperty
{
    /**
     * RequestValidateCallDataProperty constructor.
     * @throws ReflectionException
     */
    public function __construct()
    {
        parent::__construct();
        $this->validate();
    }
}