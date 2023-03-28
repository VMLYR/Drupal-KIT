<?php

namespace Drupal\rocketship_core\Plugin\Layout;

use Drupal\Core\Form\FormStateInterface;

abstract class RocketshipCoreBaseMultiColumnLayout extends RocketshipCoreBaseLayout {

  public const COLS = 0;

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $configuration = parent::defaultConfiguration();
    $configuration['col_spacing'] = '1x';
    $configuration['vertical_alignment'] = 'top';
    $configuration['col_spacing_override_column'] = NULL;
    $configuration['col_spacing_override'] = '1x';
    return $configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    if (!isset($form['columns_wrapper'])) {
      $form['columns_wrapper'] = array(
        '#type' => 'details',
        '#title' => $this->t('Columns'),
        '#open' => $this->configuration['col_spacing'] != '1x' || $this->configuration['col_spacing_override_column'],
      );
    }

    $form['columns_wrapper']['col_spacing'] = [
      '#type' => 'select',
      '#title' => $this->t('Column spacing'),
      '#description' => $this->t('How much room to leave between each column.'),
      '#default_value' => $this->configuration['col_spacing'],
      '#options' => [
        '0x' => $this->t('No space between columns'),
        '1x' => $this->t('Default spacing'),
        '2x' => $this->t('Twice as much spacing'),
        '3x' => $this->t('Three times as much spacing'),
        '4x' => $this->t('Four times as much spacing'),
        '5x' => $this->t('Five times as much spacing'),
      ],
    ];

    $options = [];
    for ($i = 1; $i <= static::COLS; $i++) {
      $options[$i] = $this->t('Column ' . $i);
    }
    $form['columns_wrapper']['col_spacing_override_column'] = [
      '#type' => 'select',
      '#title' => $this->t('Column to override'),
      '#description' => $this->t('Select a specific column to override spacing for.'),
      '#default_value' => $this->configuration['col_spacing_override_column'],
      '#options' => $options,
      '#empty_option' => $this->t('No override'),
    ];
    $form['columns_wrapper']['col_spacing_override'] = [
      '#type' => 'select',
      '#title' => $this->t('Column spacing override'),
      '#description' => $this->t('Spacing override, specifically for the selected column'),
      '#default_value' => $this->configuration['col_spacing_override'],
      '#options' => [
        '1x' => $this->t('Default spacing'),
        '2x' => $this->t('Twice as much spacing'),
        '3x' => $this->t('Three times as much spacing'),
        '4x' => $this->t('Four times as much spacing'),
        '5x' => $this->t('Five times as much spacing'),
      ],
      '#states' => [
        'invisible' => [
          ':input[name="layout_settings[col_spacing_override_column]"]' => ['value' => ''],
        ],
      ],
    ];

    if (!isset($form['alignments_wrapper'])) {
      $form['alignments_wrapper'] = array(
        '#type' => 'details',
        '#title' => $this->t('Alignment'),
        '#open' => $this->configuration['vertical_alignment'] != 'top',
      );
    }

    $form['alignments_wrapper']['vertical_alignment'] = [
      '#type' => 'radios',
      '#title' => $this->t('Vertical alignment'),
      '#description' => $this->t('How to align the columns and their content vertically.'),
      '#default_value' => $this->configuration['vertical_alignment'],
      '#options' => [
        'top' => $this->t('Top'),
        'middle' => $this->t('Middle'),
        'bottom' => $this->t('Bottom'),
      ],
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['col_spacing'] = $form_state->getValue([
      'columns_wrapper',
      'col_spacing',
    ], '1x');
    $this->configuration['col_spacing_override_column'] = $form_state->getValue([
      'columns_wrapper',
      'col_spacing_override_column',
    ], NULL);
    $this->configuration['col_spacing_override'] = $form_state->getValue([
      'columns_wrapper',
      'col_spacing_override',
    ], '1x');
    $this->configuration['vertical_alignment'] = $form_state->getValue([
      'alignments_wrapper',
      'vertical_alignment',
    ], 'top');

    parent::submitConfigurationForm($form, $form_state);
  }

}
