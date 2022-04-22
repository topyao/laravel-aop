<?php

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
     * @param ReflectionClass    $reflectionClass
     * @param ReflectionProperty $reflectionProperty
     * @param object             $object
     *
     * @return void
     */
    public function handle(ReflectionClass $reflectionClass, ReflectionProperty $reflectionProperty, object $object)
    {
        $type = $reflectionProperty->getType();
        if (is_null($type)
            || ($type instanceof ReflectionNamedType && $type->isBuiltin())
            || $type instanceof ReflectionUnionType
            || ($type->getName()) === 'Closure') {
            return;
        } else {
            $reflectionProperty->setAccessible(true);
            $reflectionProperty->setValue($object, \app()->make($type->getName()));
        }
    }
}
