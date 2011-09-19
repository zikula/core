<?php

/*
 * This file is part of the Imagine package.
 *
 * (c) Bulat Shakirzyanov <mallluhuct@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Imagine\Image;

use Imagine\Exception\InvalidArgumentException;

final class Box implements BoxInterface
{
    /**
     * @var integer
     */
    private $width;

    /**
     * @var integer
     */
    private $height;

    /**
     * Constructs the Size with given width and height
     *
     * @param integer $width
     * @param integer $height
     *
     * @throws InvalidArgumentException
     */
    public function __construct($width, $height)
    {
        if ($height < 1 || $width < 1) {
            throw new InvalidArgumentException(sprintf(
                'Length of either side cannot be 0 or negative, current size '.
                'is %sx%s', $width, $height
            ));
        }

        $this->width  = (int) $width;
        $this->height = (int) $height;
    }

    /**
     * (non-PHPdoc)
     * @see Imagine\Image\BoxInterface::getWidth()
     */
    public function getWidth()
    {
        return $this->width;
    }

    /**
     * (non-PHPdoc)
     * @see Imagine\Image\BoxInterface::getHeight()
     */
    public function getHeight()
    {
        return $this->height;
    }

    /**
     * (non-PHPdoc)
     * @see Imagine\Image\BoxInterface::scale()
     */
    public function scale($ratio)
    {
        return new Box(round($ratio * $this->width), round($ratio * $this->height));
    }

    /**
     * (non-PHPdoc)
     * @see Imagine\Image\BoxInterface::increase()
     */
    public function increase($size)
    {
        return new Box((int) $size + $this->width, (int) $size + $this->height);
    }

    /**
     * (non-PHPdoc)
     * @see Imagine\Image\BoxInterface::contains()
     */
    public function contains(BoxInterface $box, PointInterface $start = null)
    {
        $start = $start ? $start : new Point(0, 0);

        return $start->in($this) &&
            $this->width >= $box->getWidth() + $start->getX() &&
            $this->height >= $box->getHeight() + $start->getY();
    }

    /**
     * (non-PHPdoc)
     * @see Imagine\Image\BoxInterface::square()
     */
    public function square()
    {
        return $this->width * $this->height;
    }

    /**
     * (non-PHPdoc)
     * @see Imagine\Image\BoxInterface::__toString()
     */
    public function __toString()
    {
        return sprintf('%dx%d px', $this->width, $this->height);
    }
}
