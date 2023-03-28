<?php

namespace Drupal\rocketship_core\Plugin\Field\FieldFormatter;

use Drupal\Component\Utility\Html;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'breadcrumb_render' formatter.
 *
 * @FieldFormatter(
 *   id = "breadcrumb_render",
 *   label = @Translation("Breadcrumb render"),
 *   field_types = {
 *     "boolean"
 *   }
 * )
 */
class BreadcrumbRender extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
        // Implement default settings.
      ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    return parent::settingsForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];

    $summary[] = t('Output the breadcrumb when the field value evaluates to true');
    // Implement settings summary.
    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      if ($item->value == TRUE) {
        $block_manager = \Drupal::service('plugin.manager.block');
        $config = [];
        $plugin_block = $block_manager->createInstance('system_breadcrumb_block', $config);
        $render = $plugin_block->build();
        // In some cases, you need to add the cache tags/context depending on
        // the block implementation. As it's possible to add the cache tags and
        // contexts in the render method and in ::getCacheTags and
        // ::getCacheContexts methods.
        $elements[$delta] = $render;
      }
    }

    return $elements;
  }

  /**
   * Generate the output appropriate for one field item.
   *
   * @param \Drupal\Core\Field\FieldItemInterface $item
   *   One field item.
   *
   * @return string
   *   The textual output generated.
   */
  protected function viewValue(FieldItemInterface $item) {
    // The text value has no text format assigned to it, so the user input
    // should equal the output, including newlines.
    return nl2br(Html::escape($item->value));
  }

}
