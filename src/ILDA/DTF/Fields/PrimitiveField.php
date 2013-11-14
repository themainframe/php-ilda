<?php
/**
 * ILDA (International Laser Display Association) Frame Handling in PHP.
 *
 * @package  php-ilda
 * @author Damien Walsh <me@damow.net>
 */
namespace ILDA\DTF\Fields;

use ILDA\DTF;
use ILDA\DTF\Fields\FieldInterface;

/**
 * PrimitiveField
 * Represents a fixed-length sequence of bytes representing a single field.
 *
 * @since 1.0
 */
abstract class PrimitiveField extends AbstractField
{
    /**
     * @public FieldSize The size of the field.
     */
    public $size = null;

    /**
     * @param FieldSize $size The size of the field.
     */
    public function __construct(FieldSize $size)
    {
        $this->size = $size;
    }
}
