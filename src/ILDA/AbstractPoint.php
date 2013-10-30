<?php
/**
 * ILDA (International Laser Display Association) Frame Handling in PHP.
 *
 * @package  php-ilda
 * @author Damien Walsh <me@damow.net>
 */
namespace ILDA;

/**
 * Abstract Point
 *
 * @since 1.0
 */
abstract class AbstractPoint
{
    /**
     * X coordinate
     * @public int
     */
    public $x = 0;

    /**
     * Y coordinate
     * @public int
     */
    public $y = 0;

    /**
     * Blanking bit state
     * @public int
     */
    public $isBlanked = false;
}
