<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Validator\Mapping\Loader;

use Symfony\Component\Validator\Exception\MappingException;
use Symfony\Component\Validator\Mapping\ClassMetadata;

class StaticMethodLoader implements LoaderInterface
{
    protected $methodName;

    public function __construct($methodName = 'loadValidatorMetadata')
    {
        $this->methodName = $methodName;
    }

    /**
     * {@inheritDoc}
     */
    public function loadClassMetadata(ClassMetadata $metadata)
    {
        $reflClass = $metadata->getReflectionClass();

        if ($reflClass->hasMethod($this->methodName)) {
            $reflMethod = $reflClass->getMethod($this->methodName);

            if (!$reflMethod->isStatic()) {
                throw new MappingException(sprintf('The method %s::%s should be static', $reflClass->getName(), $this->methodName));
            }

            if ($reflMethod->getDeclaringClass()->getName() != $reflClass->getName()) {
                return false;
            }

            $reflMethod->invoke(null, $metadata);

            return true;
        }

        return false;
    }
}
