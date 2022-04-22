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
use Max\LaravelAop\Contracts\AspectInterface;
use Max\Utils\Pipeline;
use Psr\Container\ContainerExceptionInterface;
use ReflectionException;

trait ProxyHandler
{
    /**
     * @param string  $function
     * @param Closure $callback
     * @param array   $arguments
     *
     * @return mixed
     * @throws ReflectionException
     * @throws NotFoundException
     * @throws ContainerExceptionInterface
     */
    protected function __callViaProxy(string $function, Closure $callback, array $arguments): mixed
    {
        $joinPoint        = new JoinPoint($this, $function, $arguments, $callback);
        $reflectionMethod = new \ReflectionMethod(__CLASS__, $function);
        $aspects          = [];
        foreach ($reflectionMethod->getAttributes() as $attribute) {
            $instance = $attribute->newInstance();
            if ($instance instanceof AspectInterface) {
                $aspects[] = $instance;
            }
        }
        if (empty($aspects)) {
            return $joinPoint->process();
        }
        return (new Pipeline(app()))
            ->send($joinPoint)
            ->through($aspects)
            ->via('process')
            ->then(function(JoinPoint $joinPoint) {
                return $joinPoint->process();
            });
    }
}
