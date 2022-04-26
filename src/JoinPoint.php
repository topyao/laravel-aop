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

class JoinPoint
{
    /**
     * @param object  $object
     * @param string  $method
     * @param array   $parameters
     * @param Closure $callback
     */
    public function __construct(
        public object     $object,
        public string     $method,
        public array      $parameters,
        protected Closure $callback
    )
    {
    }

    /**
     * @return mixed
     */
    public function process(): mixed
    {
        return call_user_func_array($this->callback, $this->parameters);
    }
}
