<?php

namespace Drupal\rocketship_core\Plugin\Layout;

use Drupal\Core\Form\FormStateInterface;

/**
 * Class TwoColumnLayout.
 *
 * @package Drupal\rocketship_core\Plugin\Layout
 */
class TwoColumnLayout extends RocketshipCoreBaseMultiColumnLayout {

  public const COLS = 2;

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $configuration = parent::defaultConfiguration();
    $configuration['col_sizing'] = '1/2';
    $configuration['left_col_size_suffix'] = '--size-1-2';
    $configuration['right_col_size_suffix'] = '--size-1-2';
    $configuration['layout_reversed'] = FALSE;
    return $configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    if (!isset($form['columns_wrapper'])) {
      $form['columns_wrapper'] = [
        '#type' => 'details',
        '#title' => $this->t('Columns'),
        '#open' => TRUE,
      ];
    }
    $form['columns_wrapper']['#open'] = $this->configuration['col_spacing'] != '1x' || $this->configuration['col_spacing_override_column'] || $this->configuration['layout_reversed'] || $this->configuration['col_sizing'] != '1/2';

    $form['columns_wrapper']['layout_reversed'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Reverse the columns'),
      '#description' => $this->t('If checked, the first column becomes the second column and vice-versa. <br />On small screens (eg. phone, where you don\'t have multiple columns), the first column will always remain on top, no matter if this option is checked or not. <br /><br /><strong>Use case:</strong> if you always want an image to be on top, on a phone screen, you would always put the Image block in column 1. <br />Then you can use the \'Reverse\' option to make the Image show in the second column on normal screens.'),
      '#default_value' => $this->configuration['layout_reversed'],
      '#weight' => -2,
    ];
    $form['columns_wrapper']['col_sizing'] = [
      '#type' => 'radios',
      '#title' => $this->t('Column sizes'),
      '#default_value' => $this->configuration['col_sizing'],
      '#options' => $this->getColSizes(),
      '#required' => TRUE,
      '#weight' => -1,
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {

    $this->configuration['col_sizing'] = $form_state->getValue([
      'columns_wrapper',
      'col_sizing',
    ], $this->configuration['col_sizing']);

    $this->configuration['layout_reversed'] = $form_state->getValue([
      'columns_wrapper',
      'layout_reversed',
    ]);

    switch ($this->configuration['col_sizing']) {
      case '1/2':
        $this->configuration['left_col_size_suffix'] = '--size-1-2';
        $this->configuration['right_col_size_suffix'] = '--size-1-2';
        break;
      case '1/3':
        $this->configuration['left_col_size_suffix'] = '--size-1-3';
        $this->configuration['right_col_size_suffix'] = '--size-2-3';
        break;
      case '1/4':
        $this->configuration['left_col_size_suffix'] = '--size-1-4';
        $this->configuration['right_col_size_suffix'] = '--size-3-4';
        break;
      case '2/3':
        $this->configuration['left_col_size_suffix'] = '--size-2-3';
        $this->configuration['right_col_size_suffix'] = '--size-1-3';
        break;
      case '3/4':
        $this->configuration['left_col_size_suffix'] = '--size-3-4';
        $this->configuration['right_col_size_suffix'] = '--size-1-4';
        break;
      case '5/12':
        $this->configuration['left_col_size_suffix'] = '--size-5-12';
        $this->configuration['right_col_size_suffix'] = '--size-7-12';
        break;
      case '7/12':
        $this->configuration['left_col_size_suffix'] = '--size-7-12';
        $this->configuration['right_col_size_suffix'] = '--size-5-12';
        break;
    }

    parent::submitConfigurationForm($form, $form_state);
  }

  protected function getColSizes() {
    return [
      '1/2' => '1/2 + 1/2',
      '1/3' => '1/3 + 2/3',
      '1/4' => '1/4 + 3/4',
      '2/3' => '2/3 + 1/3',
      '3/4' => '3/4 + 1/4',
      '5/12' => '5/12 + 7/12',
      '7/12' => '7/12 + 5/12',
    ];
  }

}
