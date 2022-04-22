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

use Closure;
use Illuminate\Support\Facades\Cache;
use Max\LaravelAop\Contracts\AspectInterface;
use Max\LaravelAop\JoinPoint;

#[\Attribute(\Attribute::TARGET_METHOD)]
class Cacheable implements AspectInterface
{
    public function __construct(
        protected string $prefix = '',
        protected int    $ttl = 0
    )
    {
    }

    /**
     * @param JoinPoint $joinPoint
     * @param Closure   $next
     *
     * @return mixed
     */
    public function process(JoinPoint $joinPoint, Closure $next): mixed
    {
        return Cache::remember($this->getKey($joinPoint), $this->ttl, fn() => $next($joinPoint));
    }

    /**
     * @param JoinPoint $joinPoint
     *
     * @return string
     */
    protected function getKey(JoinPoint $joinPoint): string
    {
        $key = $this->key ?? ($joinPoint->proxy::class . ':' . $joinPoint->function . ':' . serialize(array_filter($joinPoint->arguments, fn($item) => !is_object($item))));
        return $this->prefix ? ($this->prefix . ':' . $key) : $key;
    }
}
