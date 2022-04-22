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
        foreach (AspectCollector::getClassPropertyAttributes(__CLASS__) as $property => $attributes) {
            foreach ($attributes as $attribute) {
                try {
                    /** @var PropertyAttribute $attribute */
                    $attribute->handle($reflectionClass, $reflectionClass->getProperty($property), $this);
                } catch (Throwable $throwable) {
                    throw new PropertyHandleException(
                        sprintf('Cannot inject Property %s into %s. (%s)',
                            $property, __CLASS__, $throwable->getMessage()
                        )
                    );
                }

            }
        }
    }
}
