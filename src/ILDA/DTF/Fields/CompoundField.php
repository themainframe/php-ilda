<?php
/**
 * ILDA (International Laser Display Association) Frame Handling in PHP.
 *
 * @package  php-ilda
 * @author Damien Walsh <me@damow.net>
 */
namespace ILDA\DTF\Fields;

use ILDA\DTF\Fields;
use ILDA\DTF\Streams\StreamInterface;

/**
 * CompoundField
 * A field that comprises a number of fields.
 *
 * @since 1.0
 */
class CompoundField extends AbstractField
{
    /**
     * @protected array The fields enclosed within this compound field.
     */
    protected $fields = array();

    /**
     * @protected int The number of times this compound field is repeated.
     */
    protected $count = 1;

    /**
     * @param int $count The number of times this compound field is repeated.
     */
    public function setCount($count)
    {
        $this->count = $count;
    }

    /**
     * @param AbstractField $field The field to add to the compound field.
     */
    public function addField(AbstractField $field)
    {
        $this->fields[] = $field;
    }

    /**
     * @param StreamInterface $stream The stream to read fields from.
     * @return array
     */
    public function read(StreamInterface $stream)
    {
        $readFields = array();

        // Read this compound field $count times
        for ($iteration = 0; $iteration < $this->count; $iteration ++) {

            $subFields = array();

            foreach ($this->fields as $field) {
                $subFields[] = $field->read($stream);
            }

            $readFields[] = $subFields;
        }

        return $readFields;
    }
}
