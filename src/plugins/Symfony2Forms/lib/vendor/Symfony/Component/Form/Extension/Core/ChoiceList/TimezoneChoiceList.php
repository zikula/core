<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\Form\Extension\Core\ChoiceList;

/**
 * Represents a choice list where each timezone is broken down by continent.
 *
 * @author Bernhard Schussek <bernhard.schussek@symfony.com>
 */
class TimezoneChoiceList implements ChoiceListInterface
{
    /**
     * Stores the available timezone choices
     * @var array
     */
    static protected $timezones;

    /**
     * Returns the timezone choices.
     *
     * The choices are generated from the ICU function
     * \DateTimeZone::listIdentifiers(). They are cached during a single request,
     * so multiple timezone fields on the same page don't lead to unnecessary
     * overhead.
     *
     * @return array The timezone choices
     */
    public function getChoices()
    {
        if (null !== static::$timezones) {
            return static::$timezones;
        }

        static::$timezones = array();
        foreach (\DateTimeZone::listIdentifiers() as $timezone) {
            $parts = explode('/', $timezone);

            if (count($parts) > 2) {
                $region = $parts[0];
                $name = $parts[1].' - '.$parts[2];
            } else if (count($parts) > 1) {
                $region = $parts[0];
                $name = $parts[1];
            } else {
                $region = 'Other';
                $name = $parts[0];
            }

            static::$timezones[$region][$timezone] = str_replace('_', ' ', $name);
        }

        return static::$timezones;
    }
}
