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

use Max\LaravelAop\Contracts\PropertyAttribute;
use Max\LaravelAop\Exceptions\PropertyHandleException;
use Throwable;

trait PropertyHandler
{
    /**
     * @return void
     */
    protected function __handleProperties(): void
    {
        $reflectionClass = new \ReflectionClass(__CLASS__);
        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            foreach ($reflectionProperty->getAttributes() as $attribute) {
                try {
                    $instance = $attribute->newInstance();
                    if ($instance instanceof PropertyAttribute) {
                        $instance->handle(
                            $reflectionClass, $reflectionProperty, $this
                        );
                    }
                } catch (Throwable $throwable) {
                    throw new PropertyHandleException(
                        sprintf('Cannot inject Property %s into %s. (%s)',
                            $reflectionProperty->getName(), __CLASS__, $throwable->getMessage()
                        )
                    );
                }
            }
        }
    }
}
