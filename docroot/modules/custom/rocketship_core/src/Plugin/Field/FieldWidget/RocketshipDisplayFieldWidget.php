<?php

namespace Drupal\rocketship_core\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'rs_display_field_widget' widget.
 *
 * @FieldWidget(
 *   id = "rs_display_field_widget",
 *   label = @Translation("Rocketship Display Field Widget (invisible)"),
 *   field_types = {
 *     "rs_display_field"
 *   }
 * )
 */
class RocketshipDisplayFieldWidget extends WidgetBase {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    return [];
  }

}
