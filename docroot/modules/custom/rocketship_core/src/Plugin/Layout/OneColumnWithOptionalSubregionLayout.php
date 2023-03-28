<?php

namespace Drupal\rocketship_core\Plugin\Layout;

use Drupal\Core\Form\FormStateInterface;

/**
 * Class OneColumnWithOptionalSubregionLayout.
 *
 * @package Drupal\rocketship_core\Plugin\Layout
 */
class OneColumnWithOptionalSubregionLayout extends RocketshipCoreBaseLayout {

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->updateRegions();
  }

  /**
   * Update the regions set on the plugin definition.
   */
  protected function updateRegions() {
    $regions = $this->getPluginDefinition()->getRegions();
    $this->calculateRegions($regions);
    $this->getPluginDefinition()->setRegions($regions);
  }

  /**
   * Alter existing regions.
   *
   * Here you can alter the standard regions array to
   * add or remove regions based on configuration or anything else.
   *
   * @param array $regions
   *   List of regions.
   */
  protected function calculateRegions(array &$regions) {
    if (!$this->configuration['with_subregion']) {
      if (isset($regions['subregion'])) {
        unset($regions['subregion']);
      }
    }
    else {
      $regions['subregion'] = ['label' => 'Subregion'];
    }

    if (!$this->configuration['with_subregion_02']) {
      if (isset($regions['subregion_02'])) {
        unset($regions['subregion_02']);
      }
    }
    else {
      $regions['subregion_02'] = ['label' => 'Subregion 2'];
    }
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $configuration = parent::defaultConfiguration();
    $configuration['with_subregion'] = FALSE;
    $configuration['with_subregion_02'] = FALSE;
    $configuration['subregion_position'] = 'after';
    $configuration['subregion_02_position'] = 'after';
    $configuration['section_purpose'] = 'content';
    return $configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $form['section_purpose'] = [
      '#weight' => 0,
      '#type' => 'select',
      '#title' => $this->t('Section purpose'),
      '#options' => [
        'top' => $this->t('Top'),
        'header' => $this->t('Header'),
        'content' => $this->t('Content'),
        'footer' => $this->t('Footer'),
        'bottom' => $this->t('Bottom'),
      ],
      '#default_value' => $this->getConfiguration()['section_purpose'],
      '#description' => $this->t('Defines a specific use for this Layout, which sets classes or modifies the HTML output. Most of the time, you can simply stick to Content.'),
      '#access' => $this->currentUser->hasPermission('administer minimal styling on rocketship layouts'),
      '#required' => TRUE,
    ];

    $form['subregions_wrapper'] = array(
      '#type' => 'details',
      '#title' => $this->t('Subregions'),
      '#open' => $this->getConfiguration()['with_subregion'] || $this->getConfiguration()['with_subregion_02'],
      '#access' => $this->currentUser->hasPermission('administer minimal styling on rocketship layouts'),
    );

    $form['subregions_wrapper']['subregion_01_wrapper'] = array(
      '#type' => 'details',
      '#title' => $this->t('Subregion 1'),
      '#open' => $this->getConfiguration()['with_subregion'],
      '#access' => $this->currentUser->hasPermission('administer minimal styling on rocketship layouts'),
    );

    $form['subregions_wrapper']['subregion_01_wrapper']['with_subregion'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable subregion'),
      '#default_value' => $this->getConfiguration()['with_subregion'],
      '#description' => $this->t('Choose whether or not to use a subregion.'),
      '#access' => $this->currentUser->hasPermission('administer minimal styling on rocketship layouts'),
    ];
    $form['subregions_wrapper']['subregion_01_wrapper']['subregion_position'] = [
      '#type' => 'select',
      '#title' => $this->t('Subregion position'),
      '#description' => $this->t('Choose the position of the subregions.'),
      '#default_value' => $this->getConfiguration()['subregion_position'],
      '#options' => [
        'before' => $this->t('Before'),
        'after' => $this->t('After'),
      ],
      '#access' => $this->currentUser->hasPermission('administer minimal styling on rocketship layouts'),
    ];

    $form['subregions_wrapper']['subregion_02_wrapper'] = array(
      '#type' => 'details',
      '#title' => $this->t('Subregion 2'),
      '#open' => $this->getConfiguration()['with_subregion_02'],
      '#access' => $this->currentUser->hasPermission('administer minimal styling on rocketship layouts'),
    );

    $form['subregions_wrapper']['subregion_02_wrapper']['with_subregion_02'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable subregion 2'),
      '#default_value' => $this->getConfiguration()['with_subregion_02'],
      '#description' => $this->t('Choose whether or not to use a subregion.'),
      '#access' => $this->currentUser->hasPermission('administer minimal styling on rocketship layouts'),
    ];
    $form['subregions_wrapper']['subregion_02_wrapper']['subregion_02_position'] = [
      '#type' => 'select',
      '#title' => $this->t('Subregion 2 position'),
      '#description' => $this->t('Choose the position of the subregions.'),
      '#default_value' => $this->getConfiguration()['subregion_02_position'],
      '#options' => [
        'before' => $this->t('Before'),
        'after' => $this->t('After'),
      ],
      '#access' => $this->currentUser->hasPermission('administer minimal styling on rocketship layouts'),
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['with_subregion'] = $form_state->getValue([
      'subregions_wrapper',
      'subregion_01_wrapper',
      'with_subregion',
    ], FALSE);
    $this->configuration['subregion_position'] = $form_state->getValue([
      'subregions_wrapper',
      'subregion_01_wrapper',
      'subregion_position',
    ], 'after');
    $this->configuration['with_subregion_02'] = $form_state->getValue([
      'subregions_wrapper',
      'subregion_02_wrapper',
      'with_subregion_02',
    ], FALSE);
    $this->configuration['subregion_02_position'] = $form_state->getValue([
      'subregions_wrapper',
      'subregion_02_wrapper',
      'subregion_02_position',
    ], 'after');

    parent::submitConfigurationForm($form, $form_state);
    $this->updateRegions();
  }

  /**
   * {@inheritdoc}
   */
  public function build(array $regions) {
    $this->updateRegions();
    return parent::build($regions);
  }

}
