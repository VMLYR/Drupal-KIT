<?php

namespace Drupal\rocketship_core\Plugin\Field\FieldType;

use Drupal\Component\Utility\Random;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'tabbed_field_type' field type.
 *
 * @FieldType(
 *   id = "tabbed_item",
 *   label = @Translation("Tabbed field"),
 *   default_widget = "tabbed_widget",
 *   default_formatter = "tabbed_formatter",
 *   description = @Translation("A field containing a title and body") * )
 */
class TabbedItem extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultStorageSettings() {
    return [
      'max_length' => 255,
      'title' => '',
      'value' => '',
      'format' => NULL,
    ] + parent::defaultStorageSettings();
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['title'] = DataDefinition::create('string')
      ->setLabel(t('Title'))
      ->setRequired(TRUE);

    $properties['value'] = DataDefinition::create('string')
      ->setLabel('Body value')
      ->setRequired(TRUE);

    $properties['format'] = DataDefinition::create('filter_format')
      ->setLabel(t('Text format'));

    $properties['processed'] = DataDefinition::create('string')
      ->setLabel(t('Processed text'))
      ->setDescription(t('The text with the text format applied'))
      ->setComputed(TRUE)
      ->setClass('\Drupal\text\TextProcessed')
      ->setSetting('text source', 'body');

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $schema = [
      'columns' => [
        'title' => [
          'description' => 'The link text.',
          'type' => 'varchar',
          'length' => 255,
        ],
        'value' => [
          'type' => 'text',
          'size' => 'big',
        ],
        'format' => [
          'type' => 'varchar_ascii',
          'length' => 255,
        ],
      ],
      'indexes' => [
        'format' => ['format'],
      ],
    ];

    return $schema;
  }

  /**
   * {@inheritdoc}
   */
  public static function generateSampleValue(FieldDefinitionInterface $field_definition) {
    $random = new Random();

    $body = $random->paragraphs();
    $title = $random->word(mt_rand(1, $field_definition->getSetting('max_length')));

    $values = [
      'title' => $title,
      'value' => $body,
      'format' => filter_fallback_format(),
    ];

    return $values;
  }

  /**
   * {@inheritdoc}
   */
  public function storageSettingsForm(array &$form, FormStateInterface $form_state, $has_data) {
    $elements = [];

    $elements['max_length'] = [
      '#type' => 'number',
      '#title' => t('Maximum length'),
      '#default_value' => $this->getSetting('max_length'),
      '#required' => TRUE,
      '#description' => t('The maximum length of the title field in characters.'),
      '#min' => 1,
      '#disabled' => $has_data,
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $title = $this->get('title')->getValue();
    $body = $this->get('value')->getValue();
    if (($title === NULL || $title === '') && ($body === NULL || $body === '')) {
      return TRUE;
    }
    else {
      return FALSE;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function preSave() {
    // Let the editor handle the images that may be uploaded in a tabbed field.
    if (\Drupal::moduleHandler()->moduleExists('editor')) {
      $text = $this->get('value')->getValue();
      $uuids = _editor_parse_file_uuids($text);
      _editor_record_file_usage($uuids, $this->getEntity());
    }
    parent::preSave();
  }

}
