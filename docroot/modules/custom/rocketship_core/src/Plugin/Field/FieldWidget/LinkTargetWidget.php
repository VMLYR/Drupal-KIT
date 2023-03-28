<?php

namespace Drupal\rocketship_core\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\link\Plugin\Field\FieldWidget\LinkWidget;

/**
 * Plugin implementation of the 'link_target_widget' widget.
 *
 * @FieldWidget(
 *   id = "link_target_widget",
 *   label = @Translation("Link with target"),
 *   field_types = {
 *     "link"
 *   }
 * )
 */
class LinkTargetWidget extends LinkWidget {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'target' => '',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);
    $item = $items[$delta];
    $options = $item->get('options')->getValue();
    $targets_available = [
      '_blank' => $this->t('In new window'),
      '_self' => $this->t('In same window'),
    ];
    $default_value = !empty($options['attributes']['target']) ? $options['attributes']['target'] : '';
    $element['options']['attributes']['target'] = [
      '#type' => 'select',
      '#title' => $this->t('Select a target'),
      '#options' => ['' => $this->t('- None -')] + $targets_available,
      '#default_value' => $default_value,
    ];
    return $element;
  }

}
