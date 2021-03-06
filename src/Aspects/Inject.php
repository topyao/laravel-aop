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

namespace Max\LaravelAop\Aspects;

use Max\LaravelAop\Contracts\PropertyAttribute;
use ReflectionClass;
use ReflectionNamedType;
use ReflectionProperty;
use ReflectionUnionType;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class Inject implements PropertyAttribute
{
    /**
     * @param string|null $id
     */
    public function __construct(protected ?string $id = null)
    {
    }

    /**
     * @param ReflectionClass    $reflectionClass
     * @param ReflectionProperty $reflectionProperty
     * @param object             $object
     *
     * @return void
     */
    public function handle(ReflectionClass $reflectionClass, ReflectionProperty $reflectionProperty, object $object)
    {
        if (isset($this->id)) {
            $this->setValue($reflectionProperty, $object, app()->make($this->id));
        } else {
            $type = $reflectionProperty->getType();
            if (is_null($type)
                || ($type instanceof ReflectionNamedType && $type->isBuiltin())
                || $type instanceof ReflectionUnionType
                || ($type->getName()) === 'Closure') {
                return;
            } else {
                $this->setValue($reflectionProperty, $object, \app()->make($type->getName()));
            }
        }
    }

    /**
     * @param ReflectionProperty $reflectionProperty
     * @param object             $object
     * @param mixed              $value
     *
     * @return void
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    protected function setValue(ReflectionProperty $reflectionProperty, object $object, mixed $value)
    {
        $reflectionProperty->setAccessible(true);
        $reflectionProperty->setValue($object, $value);
    }
}
