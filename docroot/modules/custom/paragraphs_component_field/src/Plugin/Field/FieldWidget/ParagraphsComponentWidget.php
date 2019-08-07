<?php

namespace Drupal\paragraphs_component_field\Plugin\Field\FieldWidget;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\paragraphs\Plugin\Field\FieldWidget\InlineParagraphsWidget;

/**
 * Plugin implementation of the 'entity_reference_revisions paragraphs' widget.
 *
 * @FieldWidget(
 *   id = "paragraphs_component",
 *   label = @Translation("Paragraphs Component"),
 *   description = @Translation("An paragraphs inline form widget to be used for single-cardinality component fields on a page."),
 *   field_types = {
 *     "entity_reference_revisions"
 *   }
 * )
 */
class ParagraphsComponentWidget extends InlineParagraphsWidget {

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);

    // Disable some fields from being changed from default.
    $elements['add_mode']['#access'] = FALSE;
    $elements['edit_mode']['#access'] = FALSE;

    // Make default paragraph required.
    $elements['default_paragraph_type']['#required'] = TRUE;

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);

    $element['#type'] = 'fieldset';
    $element['#title_display'] = 'before';
    $element['top']['#access'] = FALSE;

    return $element;
  }

}
