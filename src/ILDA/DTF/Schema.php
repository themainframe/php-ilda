<?php
/**
 * ILDA (International Laser Display Association) Frame Handling in PHP.
 *
 * @package  php-ilda
 * @author Damien Walsh <me@damow.net>
 */
namespace ILDA\DTF;

use ILDA\DTF\Fields\CompoundField;

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
     * Initialise a new schema with a definition in the form of an array of fields.
     *
     * @param array $definition The field set to initialise the schema with.
     * @param Fields\CompoundField $parent The CompoundField, if any, to populate with the schema.
     */
    public function initWithSchemaDefinition(array $definition)
    {
        foreach ($definition as $fieldName => $field) {
            $this->addDefinedField($fieldName, $field);
        }

        return $this;
    }

    private function addDefinedField($fieldName, array $definition, CompoundField $targetField = null)
    {
        $className = '\\ILDA\\DTF\\Fields\\' . $definition['_type'];
        $newField = new $className;

        // Set the properties on the field
        foreach ($definition as $propertyName => $propertyValue) {
            if ($propertyName[0] === '_') {
                // Don't add special-meaning _ fields
                continue;
            }

            $newField->{$propertyName} = $propertyValue;
        }

        // Are we adding a compound field?
        if (is_a($newField, '\\ILDA\\DTF\\Fields\\CompoundField')) {
            if (isset($definition['_fields'])) {
                // Adding a compound field that has some subfields
                foreach ($definition['_fields'] as $subFieldName => $subFieldDefinition) {
                    $this->addDefinedField($subFieldName, $subFieldDefinition, $newField);
                }
            }
        }

        if ($targetField) {
            // Adding the field to an existing compound field
            $targetField->addField($fieldName, $newField);
        } else {
            // Adding the field to this schema
            $this->addField($fieldName, $newField);
        }
    }

    /**
     * @param AbstractField $field The field to add to the schema.
     * @return $this
     */
    public function addField($fieldName, Fields\FieldInterface $field)
    {
        $this->fields[$fieldName] = $field;
        return $this;
    }

    /**
     * @param $stream StreamInterface The stream to parse.
     * @return array
     */
    public function readStream(Streams\StreamInterface $stream)
    {
        $readFields = array();

        foreach ($this->fields as $fieldName => $field) {
            $readFields[$fieldName] = $field->read($stream);
        }

        return $readFields;
    }
}
