<?php

namespace Drupal\rocketship_core\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'tabbed_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "tabbed_formatter",
 *   label = @Translation("Tabbed field formatter"),
 *   field_types = {
 *     "tabbed_item"
 *   }
 * )
 */
class TabbedFormatter extends FormatterBase {

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
    $form = parent::settingsForm($form, $form_state);

    $form['header'] = [
      '#type' => 'select',
      '#title' => t('Header tag'),
      '#options' => $this->getOptions(),
      '#default_value' => $this->getSetting('header'),
      '#description' => t('Define the header tag element that will be wrapped around this field.'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();
    $summary[] = t('Header element: @header', ['@header' => $this->getSetting('header')]);
    return $summary;
  }

  /**
   * Return the options for the select element.
   *
   * @return array
   *   The options array.
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
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    $tag = $this->getSetting('header');
    foreach ($items as $delta => $item) {

      $elements[$delta] = [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['tab-item'],
        ],
        'title' => [
          '#type' => 'inline_template',
          '#template' => '{{ value|nl2br }}',
          '#context' => ['value' => $item->title],
          '#prefix' => '<' . $tag . ' class="tab-item__title">',
          '#suffix' => '</' . $tag . '>',
        ],
        'value' => [
          '#type' => 'processed_text',
          '#text' => $item->value,
          '#format' => $item->format,
          '#langcode' => $item->getLangcode(),
          '#prefix' => '<div class="tab-item__content">',
          '#suffix' => '</div>',
        ],
      ];
    }

    return $elements;
  }

}
