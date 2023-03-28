<?php

namespace Drupal\rocketship_core\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\webform\Entity\Webform;

/**
 * Plugin implementation of the 'webform_render' formatter.
 *
 * @FieldFormatter(
 *   id = "webform_render",
 *   label = @Translation("Webform render"),
 *   field_types = {
 *     "boolean"
 *   }
 * )
 */
class WebformRender extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return parent::defaultSettings() + [
      'webform_id' => NULL,
      'redirect' => FALSE,
      'label' => '',
      'show_label' => FALSE,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {

    $elements = parent::settingsForm($form, $form_state);

    $webform = NULL;
    $webform_id = $this->getSetting('webform_id');
    if ($webform_id) {
      $webform = Webform::load($webform_id);
    }

    $elements['webform_id'] = [
      '#title' => $this->t('Webform'),
      '#type' => 'entity_autocomplete',
      '#target_type' => 'webform',
      '#required' => TRUE,
      '#default_value' => $webform,
    ];
    $elements['redirect'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Redirect to the webform'),
      '#default_value' => $this->getSetting('redirect'),
      '#return_value' => TRUE,
      '#description' => t('If your webform has multiple pages, this will change the behavior of the "Next" button. This will also affect where validation messages show up after an error.'),
    ];
    $elements['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#default_value' => $this->getSetting('label'),
      '#description' => $this->t("This block's label"),
    ];
    $elements['show_label'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show label'),
      '#default_value' => $this->getSetting('show_label'),
      '#return_value' => TRUE,
      '#description' => t('Whether or not to output the block label'),
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];

    $summary[] = t('Output a given Webform when the value evaluates to true');
    $summary[] = t('Outputting webform @id', ['@id' => $this->getSetting('webform_id')]);
    $summary[] = t('Redirect: @value', ['@value' => $this->getSetting('redirect') ? 'Yes' : 'No']);
    $summary[] = t('Label: @label', ['@label' => $this->getSetting('label')]);
    $summary[] = t('Show label: @value', ['@value' => $this->getSetting('show_label') ? 'Yes' : 'No']);

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      if ($item->value != TRUE) {
        continue;
      }
      if (empty($this->getSetting('webform_id'))) {
        continue;
      }
      $block_manager = \Drupal::service('plugin.manager.block');
      // You can hard code configuration or you load from settings.
      $config = [
        'label' => $this->getSetting('label'),
        'provider' => 'webform',
        'label_display' => $this->getSetting('show_label'),
        'webform_id' => $this->getSetting('webform_id'),
        'default_data' => '',
        'redirect' => $this->getSetting('redirect'),
      ];
      $plugin_block = $block_manager->createInstance('webform_block', $config);

      $render = $plugin_block->build();

      $elements[0] = $render;

      return $elements;
    }

    return $elements;
  }

}
