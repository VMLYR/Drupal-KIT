<?php

namespace Drupal\rocketship_core\Plugin\DataType;

use Drupal\Core\Field\FieldItemList;
use Drupal\Core\TypedData\ComputedItemListTrait;

/**
 * Class CalculatedValueList.
 *
 * FieldItemList for our calculated custom field. Always says yes.
 *
 * @package Drupal\rocketship_core\Plugin\DataType
 */
class CalculatedValueList extends FieldItemList {

  use ComputedItemListTrait;

  /**
   * {@inheritdoc}
   */
  protected function computeValue() {
    $this->list[0] = $this->createItem(0, ['value' => TRUE]);
  }

}
