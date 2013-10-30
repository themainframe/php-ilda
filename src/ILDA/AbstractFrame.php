<?php
/**
 * ILDA (International Laser Display Association) Frame Handling in PHP.
 *
 * @package  php-ilda
 * @author Damien Walsh <me@damow.net>
 */
namespace ILDA;

/**
 * Abstract Frame
 *
 * @since 1.0
 */
abstract class AbstractFrame
{
    /**
     * Frame type.
     * 0 = 3D
     * 1 = 2D
     * 2 = Colour Lookup
     * 3 = 24-bit RGB TC
     *
     * @public integer
     */
    public $ildaFrameType = -1;

    /**
     * Frame name.
     * @var string
     */
    public $ildaFrameName = '';

    /**
     * Author name.
     * @public string
     */
    public $ildaAuthorName = '';

    /**
     * Number of points in the frame.
     * @public integer
     */
    public $ildaPointCount = 0;

    /**
     * Frame number
     * @public integer
     */
    public $ildaFrameNumber = 0;

    /**
     * Total frames
     * @public integer
     */
    public $ildaTotalFrames = 0;

    /**
     * Scan head ID
     * @public integer
     */
    public $ildaScannerHeadID = 0;

    /**
     * Frame Points
     * @public array
     */
    public $points = array();
}
