<?php

namespace Drupal\rocketship_core\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldWidget\StringTextfieldWidget;
use Drupal\Core\Form\FormStateInterface;
use Drupal\rocketship_core\Plugin\Field\FieldType\ContentBlockTitleReplacement;

/**
 * Plugin implementation of the 'contentblock_title_replacement_widget' widget.
 *
 * @FieldWidget(
 *   id = "contentblock_title_replacement_widget",
 *   label = @Translation("Title Replacement Widget"),
 *   field_types = {
 *     "contentblock_title_replacement"
 *   }
 * )
 */
class ContentBlockTitleReplacementWidget extends StringTextfieldWidget {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'checkbox_title' => 'Replace the title',
      'checkbox_description' => "Replace the title on the detail page for this piece of content with a different title, which can include the following html: &lt;em&gt;&lt;/em&gt; and &lt;strong&gt;&lt;/strong&gt; Leave this unchecked to use the title of this piece of content as is.",
      'allowed_wrapper_tags' => ContentBlockTitleReplacement::getPossibleOptions(),
      'placeholder' => 'My <em>detailed</em> title',
      'wrapper_title' => 'Wrapper',
      'wrapper_description' => 'What tags to wrap this field in.',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::settingsForm($form, $form_state);

    $element['checkbox_title'] = [
      '#type' => 'textfield',
      '#title' => t('Checkbox title'),
      '#default_value' => $this->getSetting('checkbox_title'),
      '#required' => TRUE,
    ];
    $element['checkbox_description'] = [
      '#type' => 'textarea',
      '#title' => t('Checkbox description'),
      '#default_value' => $this->getSetting('checkbox_description'),
      '#description' => t('Text that will be shown below the checkbox to indicate what checking it will do and what you can use the replacement title field for. Escape the tags yourself as needed.'),
      '#required' => TRUE,
    ];
    $element['wrapper_title'] = [
      '#type' => 'textfield',
      '#title' => t('Wrapper title'),
      '#default_value' => $this->getSetting('wrapper_title'),
      '#required' => TRUE,
    ];
    $element['wrapper_description'] = [
      '#type' => 'textarea',
      '#title' => t('Wrapper description'),
      '#default_value' => $this->getSetting('wrapper_description'),
      '#description' => t('Text shown below the wrapper dropdown.'),
      '#required' => TRUE,
    ];
    $element['allowed_wrapper_tags'] = [
      '#type' => 'checkboxes',
      '#options' => ContentBlockTitleReplacement::getPossibleOptions(),
      '#default_value' => $this->getSetting('allowed_wrapper_tags'),
      '#required' => TRUE,
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();

    $checkbox_title = $this->getSetting('checkbox_title');
    if (!empty($checkbox_title)) {
      $summary[] = t('Checkbox title: @title', ['@title' => $checkbox_title]);
    }
    $description = $this->getSetting('checkbox_description');
    if (!empty($description)) {
      $summary[] = t('Checkbox description: @description', [
        '@description' => substr($description, 0, 50) . '...',
      ]);
    }
    $title = $this->getSetting('wrapper_title');
    if (!empty($title)) {
      $summary[] = t('Wrapper title: @title', ['@title' => $title]);
    }
    $description = $this->getSetting('wrapper_description');
    if (!empty($description)) {
      $summary[] = t('Wrapper description: @description', ['@description' => $description]);
    }
    $wrappers = $this->getSetting('allowed_wrapper_tags');
    if ($wrappers) {
      $summary[] = t('Allowed wrapper: @wrapper', ['@wrapper' => implode(', ', array_filter($wrappers))]);
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {

    $element = parent::formElement($items, $delta, $element, $form, $form_state);

    // Create a unique selector to use with #states.
    $selector = "{$items->getEntity()->getEntityTypeId()}_{$items->getFieldDefinition()->getName()}_delta_{$delta}";
    // Apply #states.
    $element['value']['#states'] = [
      'visible' => [
        ':input[data-state-selector="' . $selector . '"]' => ['checked' => TRUE],
      ],
    ];

    $allowed_options = array_filter($this->getSetting('allowed_wrapper_tags'));
    $element['wrapper'] = [
      '#title' => t($this->getSetting('wrapper_title')),
      '#description' => t($this->getSetting('wrapper_description')),
      '#options' => $allowed_options,
      '#default_value' => isset($items[$delta]->wrapper) ? $items[$delta]->wrapper : 'h1',
      '#type' => 'select',
    ];
    if (count($allowed_options) === 1) {
      // Set to type hidden, as there's nothing to choose from anyway.
      $element['wrapper'] = [
        '#type' => 'hidden',
        '#value' => isset($items[$delta]->wrapper) ? $items[$delta]->wrapper : key($allowed_options),
      ];
    }

    $element['replace'] = [
      '#title' => t($this->getSetting('checkbox_title')),
      '#description' => t($this->getSetting('checkbox_description')),
      '#type' => 'checkbox',
      '#default_value' => !empty($items[$delta]->replace),
      // Add our unique selector as data attribute.
      '#attributes' => [
        'data-state-selector' => $selector,
      ],
      '#weight' => -50,
    ];

    // Add our validate function.
    if (!isset($element['#element_validate'])) {
      $element['#element_validate'] = [];
    }

    $element['#element_validate'][] = [$this, 'validate'];

    return $element;
  }

  /**
   * Validate the entire element.
   */
  public function validate($element, FormStateInterface $form_state) {
    if (!empty($element['replace']['#value']) && empty($element['value']['#value'])) {
      // If replace title was checked, then the text field becomes required.
      $form_state->setError($element, t('The %field field is required.', ['%field' => $element['value']['#title']]));
    }
  }

  // Public function massageFormValues(array $values, array $form,
  // FormStateInterface $form_state) {
  // Remove the wrapper element from the value array
  // foreach ($values as $delta => &$value) {
  // $value = $value['element'];
  // }
  // return $values;
  // }.
}
