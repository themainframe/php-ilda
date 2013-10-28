<?php
/**
 * ILDA (International Laser Display Association) Frame Handling in PHP.
 *
 * @package  php-ilda
 * @author Damien Walsh <me@damow.net>
 */
namespace ILDA;

/**
 * Frame Reader.
 * Reads framefiles (.ild) and provides information about them.
 * Currently supports only 3D (type 0000) frames.
 * 
 * @since 1.0
 */
class FrameReader
{
    /**
     * File handle
     * @var resource
     */
    private $handle = null;

    // ------------------------------------------------------
    // BEGIN parameters of the current frame
    // ------------------------------------------------------

    /**
     * Points
     * @var array
     */
    public $points = array();

    /**
     * The current global byte offset.
     * @var integer
     */
    public $byteIndex = 0;

    /**
     * The first "ILDA" byte of the current frame.
     * @var integer
     */
    public $currentFrameIndex = 0;


    // ------------------------------------------------------
    // END parameters of the current frame.
    // ------------------------------------------------------

    /**
     * Helper
     * Big-endian Motorola integer to PHP integer
     */
    private function bigEndianToInt($motorola)
    {
        $binStr = '';

        for($c = 0; $c < strlen($motorola); $c ++)
        {
            $binStr .= str_pad(decbin(ord($motorola[$c])), 8, '0', STR_PAD_LEFT);
        }

        return bindec($binStr);
    }

    /**
     * Helper
     * Two's Complement integer to PHP integer
     */
    private function twosComplimentToInt($tcString)
    {
        $binStr = '';

        for($c = 0; $c < strlen($tcString); $c ++)
        {
            $binStr = str_pad(decbin(ord($tcString[$c])), 8, '0', STR_PAD_LEFT) . $binStr;
        }

        // First bit is worth -ve
        $value = pow(2, strlen($binStr) - 1);
        $value = $binStr[0] === '1' ? -$value : 0;

        // Remove that bit
        $binStr = substr($binStr, 1);


        return $value + bindec($binStr);
    }

    /**
     * Construct: Framefile_Reader
     * @param string $filename The file to parse.
     */
    public function __construct($filename)
    {
        $this->handle = fopen($filename, 'r');

        if(!$this->handle)
        {
            print 'Error: unable to open ILDA framefile ' . $filename . PHP_EOL;
            return false;
        }

        // Initial verification that this is a framefile
        // First bytes in file should be ILDA magic sequence
        if(!$this->readValidILDA())
        {
            print 'Error: No ILDA marker found at byte offset 0' . PHP_EOL;
            return false;
        }

        // Rewind to the start of the file after initial verification
        $this->byteIndex = 0;

        return $this;
    }

    /**
     * Read the next frame from the file.
     */
    public function nextFrame()
    {
        $frame = new Frame();

        // Check that "ILDA" is under the cursor
        if(!$this->readValidILDA())
        {
            print "Not currently on ILDA" . PHP_EOL;
        }

        $frame->ildaFrameType = $this->readFrameType();
        $frame->ildaFrameName = $this->readFrameName();
        $frame->ildaAuthorName = $this->readAuthorName();
        $frame->ildaPointCount = $this->readPointCount();
        $frame->ildaFrameNumber = $this->readFrameNumber();
        $frame->ildaTotalFrames = $this->readTotalFrames();
        $frame->ildaScannerHeadID = $this->readScannerHeadID();

        // Read points
        for($point = 0; $point < $frame->ildaPointCount; $point ++)
        {
            $frame->points[] = $this->readPoint();
        }

        return $frame;
    }

    private function readPoint()
    {
        $point = array();
        $point['x'] = $this->twosComplimentToInt(fread($this->handle, 2));

        // Y-axis needs postprocessing for bottom-up coordinates
        $y = $this->twosComplimentToInt(fread($this->handle, 2));
        $point['y'] = -$y;

        $point['z'] = $this->twosComplimentToInt(fread($this->handle, 2));

        // Read Status Code
        $code = str_pad(decbin(ord(fread($this->handle, 1))), 8, '0', STR_PAD_LEFT);
        $code = str_pad(decbin(ord(fread($this->handle, 1))), 8, '0', STR_PAD_LEFT) . $code;

        // Blanking bit
        $point['b'] = $code[1] == '1' ? true : false;

        $this->byteIndex += 8;

        return $point;
    }

    private function readValidILDA()
    {
        fseek($this->handle, $this->byteIndex);
        $magicILDA = fread($this->handle, 4);

        $this->byteIndex += 4;
        return $magicILDA === 'ILDA';
    }

    private function readFrameType()
    {
        fseek($this->handle, $this->byteIndex);
        $frameTypeBytes = fread($this->handle, 4);
        $this->byteIndex += 4;

        switch($frameTypeBytes)
        {
            case "\x00\x00\x00\x00":
                return 0;

            default:
                return -1;
        }
    }

    private function readFrameName()
    {
        fseek($this->handle, $this->byteIndex);
        $this->ildaFrameName = fread($this->handle, 8);
        $this->byteIndex += 8;

        return $this->ildaFrameName;
    }

    private function readAuthorName()
    {
        fseek($this->handle, $this->byteIndex);
        $this->ildaAuthorName = fread($this->handle, 8);
        $this->byteIndex += 8;

        return $this->ildaAuthorName;
    }

    private function readPointCount()
    {
        fseek($this->handle, $this->byteIndex);
        $pointCountBytes = fread($this->handle, 2);
        $this->ildaPointCount = $this->bigEndianToInt($pointCountBytes);
        $this->byteIndex += 2;

        return $this->ildaPointCount;
    }

    private function readFrameNumber()
    {
        fseek($this->handle, $this->byteIndex);
        $frameNumberBytes = fread($this->handle, 2);
        $this->ildaFrameNumber = $this->bigEndianToInt($frameNumberBytes);
        $this->byteIndex += 2;

        return $this->ildaFrameNumber;
    }

    private function readTotalFrames()
    {
        fseek($this->handle, $this->byteIndex);
        $totalFramesBytes = fread($this->handle, 2);
        $this->ildaTotalFrames = $this->bigEndianToInt($totalFramesBytes);
        $this->byteIndex += 2;

        return $this->ildaTotalFrames;
    }

    private function readScannerHeadID()
    {
        fseek($this->handle, $this->byteIndex);
        $scannerHeadIDBytes = fread($this->handle, 1);
        $this->ildaScannerHeadID = $this->bigEndianToInt($scannerHeadIDBytes);
        $this->byteIndex += 1;

        return $this->ildaScannerHeadID;
    }
}