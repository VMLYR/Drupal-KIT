<?php

namespace Drupal\rocketship_core\Plugin\Field\FieldType;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\DataDefinition;

/**
 * Plugin implementation of the 'rs_display_field' field type.
 *
 * This field has only computed properties and no schema. THat one computed
 * property also always returns one single value. This field type is to be
 * used solely for attaching formatters to it. It is a Display Field, after all.
 *
 * @FieldType(
 *   id = "rs_display_field",
 *   label = @Translation("Rocketship Display Field"),
 *   description = @Translation("This field is just a rack to hang custom
 *   formatters on that used to be DS fields"),
 *   default_formatter = "",
 *   list_class = "\Drupal\rocketship_core\Plugin\DataType\CalculatedValueList"
 * )
 */
class RocketshipDisplayField extends FieldItemBase {

  /**
   * {@inheritdoc}
   */
  public function getValue() {
    // We overwrite Map's getValue here, because it doesn't
    // ask $property->getValue() if it's calculated which
    // seems weird to me... I think I'm missing something but
    // the List class takes care of calculating the values and
    // it creates field items, so those field items should
    // act normally, no? Anyway, here we are.
    // Ah right, that's probably if you have a mix of... no,
    // even for a mixed field, like formatted text you're still supposed
    // to ask the property class for its value?
    // I guess in core if you use
    // ::getValue you don't get computed properties. Kinda weird, so you
    // have to ask them directly using the property name or else they're
    // invisible. Well, this one will return it with getValue too. Bam.
    // Update the values and return them.
    foreach ($this->properties as $name => $property) {
      $value = $property->getValue();
      // Only write NULL values if the whole map is not NULL.
      if (isset($this->values) || isset($value)) {
        $this->values[$name] = $value;
      }
    }
    return $this->values;
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties['value'] = DataDefinition::create('boolean')
      ->setLabel(new TranslatableMarkup('Value'))
      ->setComputed(TRUE);

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public static function generateSampleValue(FieldDefinitionInterface $field_definition) {
    $values['value'] = TRUE;
    return $values;
  }

  /**
   * {@inheritdoc}
   */
  public function isEmpty() {
    $value = $this->get('value')->getValue();
    return empty($value);
  }

}
