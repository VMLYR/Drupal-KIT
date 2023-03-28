<?php

namespace Drupal\rocketship_core\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'header_text_field_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "header_text_field_formatter",
 *   label = @Translation("Header text"),
 *   field_types = {
 *     "string",
 *     "string_long",
 *   }
 * )
 */
class HeaderTextFieldFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'header' => 'h2',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $setting['header'] = [
      '#type' => 'select',
      '#title' => t('Header tag'),
      '#options' => $this->getOptions(),
      '#default_value' => $this->getSetting('header'),
      '#description' => t('Define the header tag element that will be wrapped around this field.'),
    ];

    return $setting + parent::settingsForm($form, $form_state);
  }

  /**
   * Get the options.
   *
   * @return array
   *   The options.
   */
  protected function getOptions() {
    return [
      'h1' => 'h1',
      'h2' => 'h2',
      'h3' => 'h3',
      'h4' => 'h4',
      'h5' => 'h5',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $summary[] = t('Header element: @header', ['@header' => $this->getSetting('header')]);
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    // The ProcessedText element already handles cache context & tag bubbling.
    // @see \Drupal\filter\Element\ProcessedText::preRenderText()
    foreach ($items as $delta => $item) {
      $tag = $this->getSetting('header');
      // $value = '<' . $tag . '>' . $item->value . '</' . $tag . '>';.
      $elements[$delta] = [
        '#prefix' => '<' . $tag . '>',
        '#suffix' => '</' . $tag . '>',
        '#type' => 'inline_template',
        '#template' => '{{ value|nl2br }}',
        '#context' => ['value' => $item->value],
      ];
    }

    return $elements;
  }

}
