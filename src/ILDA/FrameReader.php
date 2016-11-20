<?php
/**
 * ILDA (International Laser Display Association) Frame Handling in PHP.
 *
 * @package  php-ilda
 * @author Damien Walsh <me@damow.net>
 */
namespace ILDA;

use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;

/**
 * Frame Reader.
 * Reads frame files (.ild) and provides information about them.
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

    /**
     * A logger to report the read process to.
     * @private LoggerInterface
     */
    private $logger = null;

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


    /**
     * Construct: FrameReader
     * @param string $filename The file to parse.
     * @param LoggerInterface $logger A logger
     */
    public function __construct($filename, LoggerInterface $logger = null)
    {
        // Create a null logger if no logger is provided
        if ($logger == null) {
            $logger = new NullLogger;
        }

        $this->logger = $logger;
        $this->handle = fopen($filename, 'r');

        if (!$this->handle) {
            $this->logger->error('unable to open ILDA framefile ' . $filename);
            return false;
        }

        // Initial verification that this is a framefile
        // First bytes in file should be ILDA magic sequence
        if (!$this->readValidILDA()) {
            $this->logger->warning('no ILDA magic sequence at offset 0');
            return false;
        }

        // Rewind to the start of the file after initial verification
        $this->byteIndex = 0;

        return $this;
    }


    /**
     * Read the next frame from the file.
     *
     * @return Frame2D|Frame3D
     */
    public function nextFrame()
    {
        // Check that "ILDA" is under the cursor
        if (!$this->readValidILDA()) {
            $this->logger->warning('no ILDA magic sequence found');
        }

        $frameType = $this->readFrameType();
        $this->logger->info('discovered ' . $frameType . ' type frame at offset ' . $this->byteIndex);

        $frame = null;

        switch($frameType)
        {
            case 0x00:
                $frame = new Frame3D;
                break;

            case 0x01:
                $frame = new Frame2D;
                break;

            case 0x02:
            case 0x03:

                break;

            default:
                // Not a valid frame to read here
                return null;

        }

        // Build the initial frame parameters
        $frame->ildaFrameType = $frameType;
        $frame->ildaFrameName = $this->readFrameName();
        $frame->ildaAuthorName = $this->readAuthorName();
        $frame->ildaPointCount = $this->readPointCount();
        $frame->ildaFrameNumber = $this->readFrameNumber();
        $frame->ildaTotalFrames = $this->readTotalFrames();
        $frame->ildaScannerHeadID = $this->readScannerHeadID();

        // Read points
        $this->logger->info('expecting ' . $frame->ildaPointCount . ' points');
        for ($point = 0; $point < $frame->ildaPointCount; $point ++) {
            $frame->points[] = $this->readPoint($frame);
        }

        $this->logger->info('read ' . count($frame->points) . ' points');

        // Skip the trailing NUL byte after the frame
        $this->byteIndex += 1;

        return $frame;
    }

    /**
     * Read a point from the current file cursor.
     *
     * @param AbstractFrame $frame
     * @return Point2D|Point3D
     */
    private function readPoint(AbstractFrame $frame)
    {
        // Create new point based on frame type
        $point = $frame->ildaFrameType === 0x00 ? new Point3D : new Point2D;

        // Store X-axis
        $point->x = $this->twosComplimentToInt(fread($this->handle, 2));
        $this->byteIndex += 2;

        // Y-axis needs postprocessing for bottom-up coordinates
        $point->y = -$this->twosComplimentToInt(fread($this->handle, 2));
        $this->byteIndex += 2;

        if ($frame->ildaFrameType === 0x00) {
            $point->z = $this->twosComplimentToInt(fread($this->handle, 2));
            $this->byteIndex += 2;
        }

        // Read Status Code
        // TODO: add colour parsing
        fread($this->handle, 1);

        $codeByteB = fread($this->handle, 1);
        $this->byteIndex += 2;

        // Blanking bit
        $point->isBlanked = 0b01000000 & ord($codeByteB);

        return $point;
    }

    /**
     * Check if the cursor is currently at the ILDA magic byte string.
     * Advances byteIndex automatically.
     *
     * @return bool
     */
    private function readValidILDA()
    {
        fseek($this->handle, $this->byteIndex);
        $magicILDA = fread($this->handle, 4);

        $this->byteIndex += 4;
        return $magicILDA === 'ILDA';
    }

    /**
     * Get the frame type
     * Advances byteIndex automatically.
     *
     * @return int
     */
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

    /**
     * Get the frame name label
     * Advances byteIndex automatically.
     *
     * @return string
     */
    private function readFrameName()
    {
        fseek($this->handle, $this->byteIndex);
        $ildaFrameName = fread($this->handle, 8);
        $this->byteIndex += 8;

        return $ildaFrameName;
    }

    /**
     * Get the author name
     * Advances byteIndex automatically.
     *
     * @return string
     */
    private function readAuthorName()
    {
        fseek($this->handle, $this->byteIndex);
        $ildaAuthorName = fread($this->handle, 8);
        $this->byteIndex += 8;

        return $ildaAuthorName;
    }

    /**
     * Get the point count for the frame
     * Advances byteIndex automatically.
     *
     * @return int
     */
    private function readPointCount()
    {
        fseek($this->handle, $this->byteIndex);
        $pointCountBytes = fread($this->handle, 2);
        $ildaPointCount = $this->bigEndianToInt($pointCountBytes);
        $this->byteIndex += 2;

        return $ildaPointCount;
    }

    /**
     * Get the frame index
     * Advances byteIndex automatically.
     *
     * @return int
     */
    private function readFrameNumber()
    {
        fseek($this->handle, $this->byteIndex);
        $frameNumberBytes = fread($this->handle, 2);
        $ildaFrameNumber = $this->bigEndianToInt($frameNumberBytes);
        $this->byteIndex += 2;

        return $ildaFrameNumber;
    }

    /**
     * Get the number of frames in the sequence
     * Advances byteIndex automatically.
     *
     * @return int
     */
    private function readTotalFrames()
    {
        fseek($this->handle, $this->byteIndex);
        $totalFramesBytes = fread($this->handle, 2);
        $ildaTotalFrames = $this->bigEndianToInt($totalFramesBytes);
        $this->byteIndex += 2;

        return $ildaTotalFrames;
    }

    /**
     * Get the scanner head that will draw this frame
     * Advances byteIndex automatically.
     *
     * @return int
     */
    private function readScannerHeadID()
    {
        fseek($this->handle, $this->byteIndex);
        $scannerHeadIDBytes = fread($this->handle, 1);
        $ildaScannerHeadID = $this->bigEndianToInt($scannerHeadIDBytes);
        $this->byteIndex += 1;

        return $ildaScannerHeadID;
    }

    /**
     * Helper
     *
     * Two's Complement integer to PHP integer
     *
     * @param string $tcString The string of bytes to be manipulated
     * @return integer
     */
    private function twosComplimentToInt($tcString)
    {
        $unpacked = unpack('s', $tcString);
        return $unpacked[1];
    }

    /**
     * Helper
     *
     * Big-endian Motorola integer to PHP
     *
     * @param string $motorola The string of bytes to be manipulated
     * @return integer
     */
    private function bigEndianToInt($motorola)
    {
        if (strlen($motorola) < 2) {
            $motorola = str_pad($motorola, 2, "\x00", STR_PAD_LEFT);
        }

        $unpacked = unpack('n', $motorola);
        return $unpacked[1];
    }
}
