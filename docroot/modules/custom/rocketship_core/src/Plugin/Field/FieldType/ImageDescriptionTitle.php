<?php

namespace Drupal\rocketship_core\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\image\Plugin\Field\FieldType\ImageItem;

/**
 * Plugin implementation of the 'ImageDescriptionTitle' field type.
 *
 * @FieldType(
 *   id = "imagedescriptiontitle",
 *   label = @Translation("Image description title"),
 *   description = @Translation("Image description title field contains an image, title and text (also supports icon list or numbers)"),
 *   default_widget = "imageDescriptionTitle_default_widget",
 *   default_formatter = "imageDescriptionTitle_default_formatter",
 *   column_groups = {
 *     "file" = {
 *       "label" = @Translation("File"),
 *       "columns" = {
 *         "target_id", "width", "height"
 *       },
 *       "require_all_groups_for_translation" = TRUE
 *     },
 *     "alt" = {
 *       "label" = @Translation("Alt"),
 *       "translatable" = TRUE
 *     },
 *     "title" = {
 *       "label" = @Translation("Title"),
 *       "translatable" = TRUE
 *     },
 *     "idttitle" = {
 *       "label" = @Translation("Title"),
 *       "translatable" = TRUE
 *     },
 *    "idttextarea" = {
 *       "label" = @Translation("Textarea"),
 *       "translatable" = TRUE
 *     },
 *    "idttextareaformat" = {
 *       "label" = @Translation("Textarea format"),
 *       "translatable" = TRUE
 *     },
 *   },
 *   list_class = "\Drupal\file\Plugin\Field\FieldType\FileFieldItemList",
 *   constraints = {"ReferenceAccess" = {}, "FileValidation" = {}}
 * )
 */
class ImageDescriptionTitle extends ImageItem {

  /**
   * The entity manager.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * {@inheritdoc}
   */
  public static function defaultFieldSettings() {
    $settings = parent::defaultFieldSettings();

    unset($settings['description_field']);
    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    $schema = parent::schema($field_definition);

    $schema['columns']['idttitle'] = [
      'description' => "The title as a header for the output",
      'type' => 'varchar',
      'length' => 1024,
    ];

    $schema['columns']['idttextarea'] = [
      'description' => "The textarea for the output",
      'type' => 'text',
      'size' => 'big',
    ];

    $schema['columns']['idttextareaformat'] = [
      'description' => "The textarea for the output",
      'type' => 'varchar_ascii',
      'length' => 255,
    ];

    return $schema;

  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties = parent::propertyDefinitions($field_definition);

    $properties['idttitle'] = DataDefinition::create('string')
      ->setLabel(t('Title'))
      ->setDescription(t("Title"))
      ->setRequired(TRUE);

    $properties['idttextarea'] = DataDefinition::create('string')
      ->setLabel(t('Textarea'))
      ->setRequired(TRUE);

    $properties['idttextareaformat'] = DataDefinition::create('filter_format')
      ->setLabel(t('Text format'));

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function storageSettingsForm(array &$form, FormStateInterface $form_state, $has_data) {

    $element = parent::storageSettingsForm($form, $form_state, $has_data);

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function fieldSettingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::fieldSettingsForm($form, $form_state);

    return $element;
  }

}
