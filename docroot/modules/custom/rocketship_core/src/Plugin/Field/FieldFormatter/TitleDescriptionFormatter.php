<?php

namespace Drupal\rocketship_core\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'title_description_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "title_description_formatter",
 *   label = @Translation("Title:Description"),
 *   field_types = {
 *     "title_description_field"
 *   }
 * )
 */
class TitleDescriptionFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'wrapper' => 'h2',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);

    $form['wrapper'] = [
      '#type' => 'textfield',
      '#title' => t('Wrapper'),
      '#default_value' => $this->getSetting('wrapper'),
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];

    $summary[] = t('Using wrapper @wrapper', ['@wrapper' => $this->getSetting('wrapper')]);

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      $elements[$delta] = $this->viewValue($item);
    }

    return $elements;
  }

  /**
   * View the value for a field item.
   *
   * @param \Drupal\Core\Field\FieldItemInterface $item
   *   The item to view.
   *
   * @return array
   *   Renderable array.
   */
  protected function viewValue(FieldItemInterface $item) {
    return [
      '#theme' => 'title_description_list_item',
      '#title' => $item->title,
      '#description' => $item->description,
      '#wrapper' => $this->getSetting('wrapper'),
    ];
  }

}
