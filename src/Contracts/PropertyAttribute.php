<?php

namespace Max\LaravelAop\Contracts;

use ReflectionClass;
use ReflectionProperty;

interface PropertyAttribute
{
    public function handle(ReflectionClass $reflectionClass, ReflectionProperty $reflectionProperty, object $object);
}
