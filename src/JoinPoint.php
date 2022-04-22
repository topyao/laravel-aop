<?php

declare(strict_types=1);

/**
 * This file is part of the Max package.
 *
 * (c) Cheng Yao <987861463@qq.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Max\LaravelAop;

use Closure;
use Max\Di\ReflectionManager;
use Psr\Container\ContainerExceptionInterface;
use ReflectionException;

class JoinPoint
{
    /**
     * @param object  $proxy
     * @param string  $function
     * @param array   $arguments
     * @param Closure $callback
     */
    public function __construct(
        public object  $proxy,
        public string  $function,
        public array   $arguments,
        public Closure $callback
    )
    {
    }

    /**
     * @return mixed
     * @throws NotFoundException
     * @throws ContainerExceptionInterface
     * @throws ReflectionException
     */
    public function process(): mixed
    {
        return app()->call($this->callback, $this->arguments);
    }
}
