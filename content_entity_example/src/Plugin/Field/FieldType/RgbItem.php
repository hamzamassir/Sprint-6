<?php

namespace Drupal\content_entity_example\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;

/**
 * Plugin implementation of the 'rgb' field type.
 *
 * @FieldType(
 *   id = "rgb",
 *   label = @Translation("RGB Color"),
 *   description = @Translation("Stores an RGB color as a hexadecimal string (e.g. #ff0000)."),
 *   default_widget = "text_textfield",
 *   default_formatter = "string"
 * )
 */
class RgbItem extends FieldItemBase
{
  /**
   * {@inheritdoc}
   */
    public static function schema(FieldStorageDefinitionInterface $field_definition)
    {
        return [
        'columns' => [
        'value' => [
          // A hex color will be in the format "#rrggbb", so length = 7.
          'type' => 'varchar',
          'length' => 7,
          'not null' => false,
        ],
        ],
        ];
    }

  /**
   * {@inheritdoc}
   */
    public function isEmpty()
    {
      // Retrieve the value and consider the field empty if it's NULL or empty.
        $value = $this->get('value')->getValue();
        return $value === null || $value === '';
    }

  /**
   * {@inheritdoc}
   */
    public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition)
    {
      // Define the property that holds the color value.
        $properties = [];
        $properties['value'] = DataDefinition::create('string')
        ->setLabel(new TranslatableMarkup('Hex Color'))
        ->setDescription(new TranslatableMarkup('A hexadecimal RGB color (e.g. "#ff0000").'));
        return $properties;
    }
}
