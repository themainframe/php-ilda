<?php
/**
 * ILDA (International Laser Display Association) Frame Handling in PHP.
 *
 * @package  php-ilda
 * @author Damien Walsh <me@damow.net>
 */
namespace ILDA;

/**
 * Frame
 * Represents an ILDA Laser Show Frame.
 *
 * @since 1.0
 */
class Frame
{
    /**
     * Frame type.
     * 0 = 3D
     * 1 = 2D
     * 2 = Colour Lookup
     * 3 = 24-bit RGB TC
     * @var integer
     */
    public $ildaFrameType = -1;

    /**
     * Frame name.
     * @var string
     */
    public $ildaFrameName = '';

    /**
     * Author name.
     * @var string
     */
    public $ildaAuthorName = '';

    /**
     * Number of points in the frame.
     * @var integer
     */
    public $ildaPointCount = 0;

    /**
     * Frame number
     * @var integer
     */
    public $ildaFrameNumber = 0;

    /**
     * Total frames
     * @var integer
     */
    public $ildaTotalFrames = 0;

    /**
     * Scan head ID
     * @var integer
     */
    public $ildaScannerHeadID = 0;

    /**
     * Frame Points
     * @var array
     */
    public $points = array();
}