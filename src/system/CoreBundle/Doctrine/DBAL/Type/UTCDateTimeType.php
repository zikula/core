<?php

declare(strict_types=1);

/*
 * This file is part of the Zikula package.
 *
 * Copyright Zikula - https://ziku.la/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Zikula\CoreBundle\Doctrine\DBAL\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\ConversionException;
use Doctrine\DBAL\Types\DateTimeType;

/**
 * @see https://www.doctrine-project.org/projects/doctrine-orm/en/latest/cookbook/working-with-datetime.html
 */
class UTCDateTimeType extends DateTimeType
{
    private static \DateTimeZone $utc;

    public function convertToDatabaseValue($value, AbstractPlatform $platform): mixed
    {
        if ($value instanceof \DateTimeInterface) {
            $value->setTimezone(self::getUtc());
        }

        return parent::convertToDatabaseValue($value, $platform);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): mixed
    {
        if (null === $value || $value instanceof \DateTimeInterface) {
            return $value;
        }

        $converted = \DateTime::createFromFormat($platform->getDateTimeFormatString(), $value, self::getUtc());
        if (!$converted) {
            throw ConversionException::conversionFailedFormat($value, $this->getName(), $platform->getDateTimeFormatString());
        }

        return $converted;
    }

    private static function getUtc(): \DateTimeZone
    {
        return self::$utc ??= new \DateTimeZone('UTC');
    }
}
