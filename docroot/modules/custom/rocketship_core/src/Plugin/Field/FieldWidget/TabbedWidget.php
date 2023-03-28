<?php

namespace Drupal\rocketship_core\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\text\Plugin\Field\FieldWidget\TextareaWidget;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'tabbed_widget' widget.
 *
 * @FieldWidget(
 *   id = "tabbed_widget",
 *   label = @Translation("Tabbed field widget"),
 *   field_types = {
 *     "tabbed_item"
 *   }
 * )
 */
class TabbedWidget extends TextareaWidget {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'size' => 60,
      'placeholder' => '',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    // Get textarea settings.
    $element = parent::settingsForm($form, $form_state);

    $element['size'] = [
      '#type' => 'number',
      '#title' => t('Size of textfield'),
      '#default_value' => $this->getSetting('size'),
      '#required' => TRUE,
      '#min' => 1,
    ];
    $element['placeholder'] = [
      '#type' => 'textfield',
      '#title' => t('Placeholder'),
      '#default_value' => $this->getSetting('placeholder'),
      '#description' => t('Text that will be shown inside the field until a value is entered. This hint is usually a sample value or a brief description of the expected format.'),
    ];
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = parent::settingsSummary();

    $summary[] = t('Textfield size: @size', ['@size' => $this->getSetting('size')]);
    $placeholder = $this->getSetting('placeholder');
    if (!empty($placeholder)) {
      $summary[] = t('Placeholder: @placeholder', ['@placeholder' => $placeholder]);
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = [];
    $count = $delta + 1;

    // Let StringTextArea handle the textarea widget.
    $text = parent::formElement($items, $delta, $element, $form, $form_state);
    $title = isset($items[$delta]->title) ? $items[$delta]->title : '';

    $element['container'] = [
      '#title' => t('Faq item @delta: @title', ['@delta' => $count, '@title' => $title]),
      '#type' => 'details',
    ];

    $element['container']['title'] = [
      '#type' => 'textfield',
      '#title' => t('Title'),
      '#description' => t('Enter the title for this tab'),
      '#default_value' => $title,
      '#size' => $this->getSetting('size'),
      '#placeholder' => $this->getSetting('placeholder'),
      '#maxlength' => $this->getFieldSetting('max_length'),
      '#attributes' => [
        'class' => [
          'js-text-full',
          'text-full',
          'tabbed-title',
        ],
      ],
      '#weight' => 0,
    ];
    $element['container']['text'] = $text;
    $element['container']['text']['#title'] = t('Body');

    // Attach library which automatically change the tab title.
    $element['#attached']['library'][] = 'rocketship_core/tabbed-item';

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    $values = parent::massageFormValues($values, $form, $form_state);
    // Remove the container from each value.
    $new_values = [];
    foreach ($values as $key => $value) {
      if (isset($value['container'])) {
        $new_values[$key] = [
          'title' => $value['container']['title'],
          'value' => $value['container']['text']['value'],
          'format' => $value['container']['text']['format'],
        ];
      }
    }
    return $new_values;

  }

}
