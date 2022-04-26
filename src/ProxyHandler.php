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
use Max\Utils\Pipeline;
use Psr\Container\ContainerExceptionInterface;
use ReflectionException;

trait ProxyHandler
{
    /**
     * @param string  $method
     * @param Closure $callback
     * @param array   $parameters
     *
     * @return mixed
     * @throws ReflectionException
     * @throws ContainerExceptionInterface
     */
    protected function __callViaProxy(string $method, Closure $callback, array $parameters): mixed
    {
        return (new Pipeline(app()))
            ->send(new JoinPoint($this, $method, $parameters, $callback))
            ->through(AspectCollector::getMethodAspects(__CLASS__, $method))
            ->via('process')
            ->then(function(JoinPoint $joinPoint) {
                return $joinPoint->process();
            });
    }
}
