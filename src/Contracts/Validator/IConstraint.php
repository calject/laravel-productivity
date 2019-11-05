<?php
/**
 * Author: 沧澜
 * Date: 2019-10-14
 */

namespace Calject\LaravelProductivity\Contracts\Validator;

/**
 * Interface IConstraint
 * @package Calject\LaravelProductivity\Contracts\Validator
 */
interface IConstraint
{
    /**
     * @param mixed ...$args
     * @return array
     */
    public function getRules(... $args): array;
}