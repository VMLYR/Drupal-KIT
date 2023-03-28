<?php

namespace Drupal\rocketship_core\Plugin\Layout;

use Drupal\Core\Form\FormStateInterface;

/**
 * Class CustomLayouts.
 *
 * @package Drupal\rocketship_core\Plugin\Layout
 */
class CarouselLayout extends RocketshipCoreBaseLayout {

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $configuration = parent::defaultConfiguration();
    $configuration += [
      'vertical_alignment' => 'top',
      'autoplay' => FALSE,
      'slidesToShow_large_screen' => 5,
      'slidesToShow_medium_screen' => 4,
      'slidesToShow_tablet' => 3,
      'slidesToShow_xl_phone' => 2,
      'slidesToShow_phone' => 1,
    ];
    return $configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    // Check if slides to show is default or not
    $default_configuration = $this->defaultConfiguration();
    $default =
      $this->configuration['slidesToShow_large_screen'] == $default_configuration['slidesToShow_large_screen']
      && $this->configuration['slidesToShow_medium_screen'] == $default_configuration['slidesToShow_medium_screen']
      && $this->configuration['slidesToShow_tablet'] == $default_configuration['slidesToShow_tablet']
      && $this->configuration['slidesToShow_xl_phone'] == $default_configuration['slidesToShow_xl_phone']
      && $this->configuration['slidesToShow_phone'] == $default_configuration['slidesToShow_phone'];

    $form['breakpoint'] = [
      '#type' => 'details',
      '#title' => $this->t('Slides to show'),
      '#open' => !$default,
    ];
    $form['breakpoint']['slidesToShow_large_screen'] = [
      '#type' => 'number',
      '#min' => 1,
      '#max' => 10,
      '#title' => $this->t('large screen'),
      '#description' => $this->t('How many slides to show at once in the carousel on a large screen.'),
      '#default_value' => $this->configuration['slidesToShow_large_screen'],
      '#required' => TRUE,
    ];
    $form['breakpoint']['slidesToShow_medium_screen'] = [
      '#type' => 'number',
      '#min' => 1,
      '#max' => 10,
      '#title' => $this->t('medium screen'),
      '#description' => $this->t('How many slides to show at once in the carousel on a medium screen.'),
      '#default_value' => $this->configuration['slidesToShow_medium_screen'],
      '#required' => TRUE,
    ];
    $form['breakpoint']['slidesToShow_tablet'] = [
      '#type' => 'number',
      '#min' => 1,
      '#max' => 10,
      '#title' => $this->t('tablet'),
      '#description' => $this->t('How many slides to show at once in the carousel on a tablet.'),
      '#default_value' => $this->configuration['slidesToShow_tablet'],
      '#required' => TRUE,
    ];
    $form['breakpoint']['slidesToShow_xl_phone'] = [
      '#type' => 'number',
      '#min' => 1,
      '#max' => 10,
      '#title' => $this->t('XL phone'),
      '#description' => $this->t('How many slides to show at once in the carousel on a large phone.'),
      '#default_value' => $this->configuration['slidesToShow_xl_phone'],
      '#required' => TRUE,
    ];
    $form['breakpoint']['slidesToShow_phone'] = [
      '#type' => 'number',
      '#min' => 1,
      '#max' => 10,
      '#title' => $this->t('phone'),
      '#description' => $this->t('How many slides to show at once in the carousel on a phone.'),
      '#default_value' => $this->configuration['slidesToShow_phone'],
      '#required' => TRUE,
    ];

    $form['autoplay'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Autoplay'),
      '#description' => $this->t('Whether or not the carousel should transition on its own.'),
      '#default_value' => $this->configuration['autoplay'],
    ];

    if (!isset($form['alignments_wrapper'])) {
      $form['alignments_wrapper'] = array(
        '#type' => 'details',
        '#title' => $this->t('Alignment'),
        '#open' => TRUE,
      );
    }

    $form['alignments_wrapper']['vertical_alignment'] = [
      '#type' => 'radios',
      '#title' => $this->t('Vertical alignment'),
      '#description' => $this->t('How to align content inside the columns.'),
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
    $this->configuration['slidesToShow_large_screen'] = $form_state->getValue([
      'breakpoint',
      'slidesToShow_large_screen',
    ], 5);
    $this->configuration['slidesToShow_medium_screen'] = $form_state->getValue([
      'breakpoint',
      'slidesToShow_medium_screen',
    ], 4);
    $this->configuration['slidesToShow_tablet'] = $form_state->getValue([
      'breakpoint',
      'slidesToShow_tablet',
    ], 3);
    $this->configuration['slidesToShow_xl_phone'] = $form_state->getValue([
      'breakpoint',
      'slidesToShow_xl_phone',
    ], 2);
    $this->configuration['slidesToShow_phone'] = $form_state->getValue([
      'breakpoint',
      'slidesToShow_phone',
    ], 1);
    $this->configuration['autoplay'] = $form_state->getValue('autoplay', FALSE);

    $this->configuration['vertical_alignment'] = $form_state->getValue([
      'alignments_wrapper',
      'vertical_alignment',
    ], 'top');

    parent::submitConfigurationForm($form, $form_state);
  }

  public function build(array $regions) {
    $build = parent::build($regions);

    $build['#attached']['drupalSettings']['rocketshipUI_layout_carousel'] = $this->configuration;

    return $build;
  }

}
