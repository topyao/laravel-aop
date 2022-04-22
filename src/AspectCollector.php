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

use Max\LaravelAop\Contracts\AspectInterface;
use Max\LaravelAop\Contracts\PropertyAttribute;

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
     * @param string          $class
     * @param string          $method
     * @param AspectInterface $aspect
     *
     * @return void
     */
    public static function collectMethod(string $class, string $method, AspectInterface $aspect): void
    {
        self::$container['method'][$class][$method][] = $aspect;
    }

    /**
     * @param string            $class
     * @param string            $property
     * @param PropertyAttribute $propertyAttribute
     *
     * @return void
     */
    public static function collectProperty(string $class, string $property, PropertyAttribute $propertyAttribute): void
    {
        self::$container['property'][$class][$property][] = $propertyAttribute;
    }

    /**
     * @param string $class
     * @param string $property
     *
     * @return array
     */
    public static function getPropertyAttributes(string $class, string $property): array
    {
        return self::$container['property'][$class][$property] ?? [];
    }

    /**
     * @param string $class
     *
     * @return array
     */
    public static function getClassPropertyAttributes(string $class): array
    {
        return self::$container['property'][$class] ?? [];
    }

    /**
     * @param string $class
     * @param string $method
     *
     * @return array
     */
    public static function getMethodAspects(string $class, string $method): array
    {
        $classAspect = self::$container['class'] ?? [];
        return [...(self::$container['method'][$class][$method] ?? []), ...$classAspect];
    }

    /**
     * @return string
     */
    public static function export(): string
    {
        return serialize(self::$container);
    }

    /**
     * @param string $cache
     *
     * @return void
     */
    public static function import(string $cache): void
    {
        self::$container = unserialize($cache);
    }
}
