<?php
/**
 * ILDA (International Laser Display Association) Frame Handling in PHP.
 *
 * @package  php-ilda
 * @author Damien Walsh <me@damow.net>
 */
namespace ILDA\DTF;

/**
 * Schema
 * Represents the internal structure of a binary field file.
 *
 * @since 1.0
 */
class Schema
{
    /**
     * @var array The fields contained by this schema.
     */
    public $fields = array();

    /**
     * Initialise a new schema with an array of fields.
     *
     * @param array $fields The field set to initialise the schema with.
     */
    public function initWithArray(array $fields)
    {
        $this->fields = $fields;
    }

    /**
     * @param AbstractField $field The field to add to the schema.
     * @return $this
     */
    public function addField(Fields\AbstractField $field)
    {
        $this->fields[] = $field;
        return $this;
    }

    /**
     * @param $stream StreamInterface The stream to parse.
     * @return array
     */
    public function readStream(Streams\StreamInterface $stream)
    {
        $readFields = array();

        foreach ($this->fields as $field) {
            $readFields[] = $field->read($stream);
        }

        return $readFields;
    }
}
