<?php
/**
 * ILDA (International Laser Display Association) Frame Handling in PHP.
 *
 * @package  php-ilda
 * @author Damien Walsh <me@damow.net>
 */
namespace ILDA\DTF\Fields;

use ILDA\DTF\Streams\StreamInterface;

/**
 * TextField
 * Field.
 *
 * @since 1.0
 */
class Text extends PrimitiveField
{
    public function read(StreamInterface $stream)
    {
        $bytes = $stream->read($this->size->getBytes());
        return strval($bytes);
    }
}
