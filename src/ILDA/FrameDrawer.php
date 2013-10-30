<?php
/**
 * ILDA (International Laser Display Association) Framefile parser.
 *
 * @package  php-ilda
 * @author Damien Walsh <me@damow.net>
 */
namespace ILDA;

/**
 * Frame Drawer
 * Draws ILDA frames with the GD imaging library.
 * 
 * @since 1.0
 */
class FrameDrawer
{
    /**
     * The frame being rendered.
     * @private AbstractFrame
     */
    private $frame = null;

    /**
     * The canvas being worked on.
     * @private resource
     */
    private $canvas = null;

    /**
     * Creates a new FrameDrawer
     *
     * @param AbstractFrame $frame The frame to draw.
     */
    public function __construct(AbstractFrame $frame)
    {
        $this->frame = $frame;
    }

    /**
     * Draw the frame on a GD canvas
     *
     * @param $sizeX int
     * @param $sizeY int
     */
    public function render($sizeX, $sizeY)
    {
        // Start a new GD Canvas
        $this->canvas = imagecreatetruecolor($sizeX, $sizeY);
        $draw_colour = imagecolorallocate($this->canvas, 0, 0, 0);
        $bgColour = imagecolorallocate($this->canvas, 255, 255, 255);
        imagefill($this->canvas, 0, 0, $bgColour);

        $lastPositionX = $lastPositionY = 0;

        foreach ($this->frame->points as $point) {
            $imageXPosition = $this->map($point->x, -32768, 32767, 0, $sizeX - 1);
            $imageYPosition = $this->map($point->y, -32768, 32767, 0, $sizeY - 1);
            $blankingState = $point->isBlanked;

            if (!$blankingState) {
                imageline(
                    $this->canvas,
                    $lastPositionX,
                    $lastPositionY,
                    $imageXPosition,
                    $imageYPosition,
                    $draw_colour
                );
            }

            $lastPositionX = $imageXPosition;
            $lastPositionY = $imageYPosition;
        }
    }

    /**
     * Save the frame as a PNG.
     *
     * @param $filename
     * @return boolean
     */
    public function savePNG($filename)
    {
        if (!$this->canvas) {
            return false;
        }

        imagepng($this->canvas, $filename);
        return true;
    }

    /**
     * Maps a range of values to another range.
     *
     * @param $value float The value to map
     * @param $inMin float The minimal input
     * @param $inMax float The maximal input
     * @param $outMin float The minimal output
     * @param $outMax float The maximal output
     * @return float
     */
    private function map($value, $inMin, $inMax, $outMin, $outMax)
    {
        return ($value - $inMin) * ($outMax - $outMin) / ($inMax - $inMin) + $outMin;
    }
}
