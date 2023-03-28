<?php

namespace Drupal\rocketship_core\Plugin\Field\FieldFormatter;

use Drupal\Component\Utility\Html;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;

/**
 * Plugin implementation of the 'label_value_formatter' formatter.
 *
 * @FieldFormatter(
 *   id = "label_value_table_formatter",
 *   label = @Translation("Label:value (table view)"),
 *   field_types = {
 *     "label_value_field"
 *   }
 * )
 */
class LabelValueTableFormatter extends LabelValueFormatter {

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $rows = $header = $elements = [];
    $show_only_promoted = $this->getSetting('show_only_promoted');

    // Gotta add custom logic to add it, because we only return one "delta"
    // technically.
    $limit = $this->getThirdPartySetting('ds', 'ds_limit');
    if (is_null($limit) || $limit === '') {
      $limit = INF;
    }

    $i = 0;
    foreach ($items as $delta => $item) {
      if ($show_only_promoted && !$item->promoted) {
        continue;
      }
      $rows[$delta] = $this->viewValue($item);
      $i++;
      if ($i >= $limit) {
        break;
      }
    }

    if (!empty($rows)) {
      $elements[0] = [
        '#theme' => 'table',
        '#header' => $header,
        '#rows' => $rows,
        '#attributes' => ['class' => ['label-value-table']],
        '#header_columns' => 4,
      ];
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
      Html::escape($item->label),
      Html::escape($item->value),
    ];
  }

}
