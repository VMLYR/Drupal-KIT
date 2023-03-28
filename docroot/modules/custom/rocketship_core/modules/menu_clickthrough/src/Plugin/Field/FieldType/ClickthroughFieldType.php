<?php

namespace Drupal\menu_clickthrough\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'clickthrough_field_type' field type.
 *
 * @FieldType(
 *   id = "clickthrough_field_type",
 *   label = @Translation("Clickthrough field type"),
 *   description = @Translation("Provides a menu clickthrough overview"),
 *   default_widget = "clickthrough_widget",
 *   default_formatter = "clickthrough_formatter"
 * )
 */
class ClickthroughFieldType extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['menu'] = DataDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Menu name'))
      ->setSetting('default', '');

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $schema = [
      'columns' => [
        'menu' => [
          'type' => 'varchar',
          'length' => 191,
        ],
      ],
    ];

    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  public function getConstraints() {
    $constraints = parent::getConstraints();

    return $constraints;
  }

  /**
   * {@inheritdoc}
   */
  public static function generateSampleValue(FieldDefinitionInterface $field_definition) {
    $values['menu'] = 'main';
    return $values;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $value = $this->get('menu')->getValue();
    return $value === NULL || $value === ':';
  }

}
