<?php
/**
 * ILDA (International Laser Display Association) Frame Handling in PHP.
 *
 * @package  php-ilda
 * @author Damien Walsh <me@damow.net>
 */
namespace ILDA\DTF\Fields;

/**
 * Field Size
 * Represents the size of a field in either bits or bytes.
 *
 * @since 1.0
 */
class FieldSize
{
    /**
     * @public boolean Indicates that the field represents bits, not bytes.
     */
    private $inBits = null;

    /**
     * @public int The size of the field.
     */
    private $size = 0;

    /**
     * @param int $size The size of the field.
     * @param bool $inBits Specify that the size is in bits, not bytes.
     */
    public function __construct($size = 0, $inBits = false)
    {
        $this->size = $size;
        $this->inBits = $inBits;
    }

    /**
     * @return bool
     */
    public function isBits()
    {
        return (bool)$this->inBits;
    }

    /**
     * Get the number of bytes represented by this FieldSize.
     *
     * @return int
     */
    public function getBytes()
    {
        return $this->inBits ? (int)($this->size / 8) : $this->size;
    }

    /**
     * Get the number of bits represented by this FieldSize.
     *
     * @return int
     */
    public function getBits()
    {
        return $this->inBits ? $this->size : $this->size * 8;
    }
}
