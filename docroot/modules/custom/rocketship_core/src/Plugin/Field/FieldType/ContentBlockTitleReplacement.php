<?php

namespace Drupal\rocketship_core\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Field\Plugin\Field\FieldType\StringItem;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'contentblock_title_replacement' field type.
 *
 * @FieldType(
 *   id = "contentblock_title_replacement",
 *   label = @Translation("Current Node Title replacement"),
 *   description = @Translation("Special field that grabs the current node and uses that title if this field is empty"),
 *   default_widget = "contentblock_title_replacement_widget",
 *   default_formatter = "title_replacement_formatter"
 * )
 */
class ContentBlockTitleReplacement extends StringItem {

  /**
   * Allowed options for wrapper.
   *
   * @return array
   *   Array containing the possible options.
   */
  public static function getPossibleOptions() {
    return [
      'h1' => 'h1',
      'h2' => 'h2',
      'h3' => 'h3',
      'h4' => 'h4',
      'h5' => 'h5',
      'h6' => 'h6',
      'span' => 'span',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties = parent::propertyDefinitions($field_definition);

    $properties['replace'] = DataDefinition::create('boolean')
      ->setLabel(t('Boolean value'))
      ->setRequired(TRUE);

    $properties['wrapper'] = DataDefinition::create('string')
      ->setLabel(t('Wrapper'))
      ->addConstraint('Length', ['max' => 255])
      ->setRequired(TRUE);

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {

    $schema = parent::schema($field_definition);

    $schema['columns']['replace'] = [
      'type' => 'int',
      'size' => 'tiny',
    ];
    $schema['columns']['wrapper'] = [
      'type' => 'varchar',
      'length' => 255,
    ];

    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  public static function generateSampleValue(FieldDefinitionInterface $field_definition) {

    $values = parent::generateSampleValue($field_definition);
    $values['replace'] = mt_rand(0, 1);
    $values['wrapper'] = 'h1';
    return $values;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    // If the checkbox wasn't checked, consider this field empty.
    $value = $this->get('replace')->getValue();
    return $value === NULL || $value === '' || $value === FALSE;
  }

}
