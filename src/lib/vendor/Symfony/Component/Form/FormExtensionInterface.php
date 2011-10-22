<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Form;

/**
 * Interface for extensions which provide types, type extensions and a guesser.
 */
interface FormExtensionInterface
{
    /**
     * Returns a type by name.
     *
     * @param string $name The name of the type
     *
     * @return FormTypeInterface The type
     */
    function getType($name);

    /**
     * Returns whether the given type is supported.
     *
     * @param string $name The name of the type
     *
     * @return Boolean Whether the type is supported by this extension
     */
    function hasType($name);

    /**
     * Returns the extensions for the given type.
     *
     * @param string $name The name of the type
     *
     * @return array An array of extensions as FormTypeExtensionInterface instances
     */
    function getTypeExtensions($name);

    /**
     * Returns whether this extension provides type extensions for the given type.
     *
     * @param string $name The name of the type
     *
     * @return Boolean Whether the given type has extensions
     */
    function hasTypeExtensions($name);

    /**
     * Returns the type guesser provided by this extension.
     *
     * @return FormTypeGuesserInterface|null The type guesser
     */
    function getTypeGuesser();
}
