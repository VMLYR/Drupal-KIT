<?php

namespace Drupal\rocketship_core\Plugin\Field\FieldFormatter;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'label_value_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "label_value_formatter",
 *   label = @Translation("Label:value"),
 *   field_types = {
 *     "label_value_field"
 *   }
 * )
 */
class LabelValueFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'show_only_promoted' => FALSE,
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $form = parent::settingsForm($form, $form_state);

    $form['show_only_promoted'] = [
      '#title' => $this->t('Show only promoted field items.'),
      '#type' => 'checkbox',
      '#default_value' => $this->getSetting('show_only_promoted'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];

    if ($this->getSetting('show_only_promoted')) {
      $summary[] = $this->t('Show only promoted field items');
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];
    $show_only_promoted = $this->getSetting('show_only_promoted');

    foreach ($items as $delta => $item) {
      if ($show_only_promoted && !$item->promoted) {
        continue;
      }
      $elements[$delta] = $this->viewValue($item);
    }

    return $elements;
  }

  /**
   * Generate the output appropriate for one field item.
   *
   * @param \Drupal\Core\Field\FieldItemInterface $item
   *   One field item.
   *
   * @return array
   *   The textual output generated.
   */
  protected function viewValue(FieldItemInterface $item) {
    return [
      '#theme' => 'label_value_list_item',
      '#label' => Xss::filter($item->label),
      '#value' => Xss::filter($item->value),
      '#promoted' => $item->promoted,
    ];
  }

}
