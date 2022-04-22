<?php

namespace Max\LaravelAop\Aspects;

use Closure;
use Max\LaravelAop\Contracts\AspectInterface;
use Max\LaravelAop\JoinPoint;

#[\Attribute(\Attribute::TARGET_METHOD)]
class Cacheable implements AspectInterface
{
    /**
     * @param JoinPoint $joinPoint
     * @param Closure   $next
     *
     * @return mixed
     */
    public function process(JoinPoint $joinPoint, Closure $next): mixed
    {
        echo 'Before hello.';
        $e = $next($joinPoint);
        echo 'After hello.';
        return $e;
    }
}
