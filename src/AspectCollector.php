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

class AspectCollector
{
    /**
     * @var array
     */
    protected static array $container = [];

    /**
     * @param string $class
     * @param string $aspect
     *
     * @return void
     */
    public static function collectClass(string $class, string $aspect): void
    {
        self::$container['class'][$class][] = $aspect;
    }

    /**
     * @param string $class
     * @param string $method
     * @param string $aspect
     *
     * @return void
     */
    public static function collectMethod(string $class, string $method, string $aspect): void
    {
        self::$container['method'][$class][$method][] = $aspect;
    }
}