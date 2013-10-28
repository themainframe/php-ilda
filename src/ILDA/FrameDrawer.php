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
     * @var Frame
     */
    private $frame = null;

    /**
     * The canvas being worked on.
     * @var resource
     */
    private $canvas = null;

    private function map($value, $inMin, $inMax, $outMin, $outMax)
    {
        return ($value - $inMin) * ($outMax - $outMin) / ($inMax - $inMin) + $outMin;
    }

    public function __construct(FrameReader $frame)
    {
        $this->frame = $frame;
    }

    public function render($sizeX, $sizeY)
    {
        // Start a new GD Canvas
        $this->canvas = imagecreatetruecolor($sizeX, $sizeY);
        $draw_colour = imagecolorallocate($this->canvas, 0, 0, 0);
        $bgColour = imagecolorallocate($this->canvas, 255, 255, 255);
        imagefill($this->canvas, 0, 0, $bgColour);

        $blankingState = true;
        $lastPositionX = $lastPositionY = 0;

        foreach($this->frame->points as $point)
        {
            $imageXPosition = $this->map($point['x'], -32768, 32767, 0, $sizeX - 1);
            $imageYPosition = $this->map($point['y'], -32768, 32767, 0, $sizeY - 1);

            if(!$blankingState)
            {
                imageline($this->canvas, $lastPositionX, $lastPositionY, $imageXPosition, $imageYPosition, $draw_colour);
            }

            $blankingState = $point['b'];
            $lastPositionX = $imageXPosition;
            $lastPositionY = $imageYPosition;

        }
    }

    public function savePNG($filename)
    {
        imagepng($this->canvas);
    }
}