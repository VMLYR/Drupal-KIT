<?php

namespace Drupal\rocketship_core\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\Plugin\Field\FieldWidget\StringTextfieldWidget;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'contentblock_title_replacement_widget' widget.
 *
 * @FieldWidget(
 *   id = "tag_selection_textfield_widget",
 *   label = @Translation("Tag Selection Textfield"),
 *   field_types = {
 *     "string"
 *   }
 * )
 */
class TagSelectionTextfieldWidget extends StringTextfieldWidget {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
        'allowed_wrapper_tags' => static::tagOptions(),
        'wrapper_title' => 'Wrapper',
        'wrapper_description' => 'What tags to wrap this field in.',
      ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::settingsForm($form, $form_state);

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
      '#options' => static::tagOptions(),
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

    $allowed_options = array_filter($this->getSetting('allowed_wrapper_tags'));
    $element['wrapper'] = [
      '#title' => t($this->getSetting('wrapper_title')),
      '#description' => t($this->getSetting('wrapper_description')),
      '#options' => $allowed_options,
      '#type' => 'select',
    ];

    if (isset($items[$delta]->value)) {
      $value = $items[$delta]->value;
      [$value, $wrapper] = array_pad(explode('***', $value), 2, NULL);
      $element['value']['#default_value'] = $value;
      $element['wrapper']['#default_value'] = $wrapper;
    }

    if (count($allowed_options) === 1) {
      // Set to type hidden, as there's nothing to choose from anyway.
      $element['wrapper']['#type'] = 'hidden';
      $element['wrapper']['#value'] = key($allowed_options);
    }

    return $element;
  }

  /**
   * @return string[]
   */
  public static function tagOptions() {
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

  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    foreach ($values as &$value) {
      $value['value'] .= '***' . $value['wrapper'];
    }

    return parent::massageFormValues($values, $form, $form_state);
  }

}
