<?php

namespace Drupal\rocketship_blocks\Plugin\Block;

use Drupal\Component\Utility\Html;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'SearchRedirectBlock' block.
 *
 * @Block(
 *  id = "search_redirect_block",
 *  admin_label = @Translation("Search redirect block"),
 *  category = "Rocketship Filters"
 * )
 */
class SearchRedirectBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * Form builder service.
   *
   * @var \Drupal\Core\Form\FormBuilderInterface
   */
  protected $formBuilder;

  /**
   * SearchRedirectBlock constructor.
   *
   * @param array $configuration
   *   Configuration.
   * @param string $plugin_id
   *   Plugin ID.
   * @param string $plugin_definition
   *   Plugin definition.
   * @param \Drupal\Core\Form\FormBuilderInterface $formBuilder
   *   Form builder.
   */
  public function __construct(
    array $configuration,
    $plugin_id,
    $plugin_definition,
    FormBuilderInterface $formBuilder
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->formBuilder = $formBuilder;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('form_builder')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'redirect_path' => '<current>',
      'query_key' => 'keys',
      'reset' => FALSE,
      'placeholder_text' => '',
    ] + parent::defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form['query_key'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Query key'),
      '#description' => $this->t('What query key to append this block\'s value to'),
      '#default_value' => $this->configuration['query_key'],
      '#weight' => '0',
      '#required' => TRUE,
    ];
    $form['redirect_path'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Redirect to'),
      '#description' => $this->t(Html::escape("Where to redirect to upon form submission. Enter <current> to refresh the current page.")),
      '#default_value' => $this->configuration['redirect_path'],
      '#weight' => '0',
      '#required' => TRUE,
    ];

    $form['reset'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show reset button'),
      '#description' => $this->t('Will redirect to the redirect_path with no query arguments'),
      '#default_value' => $this->configuration['reset'],
      '#weight' => '0',
    ];

    $form['placeholder_text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Placeholder text'),
      '#description' => $this->t('Optional placeholder text added to the search input element'),
      '#default_value' => $this->configuration['placeholder_text'],
      '#weight' => '0',
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['query_key'] = $form_state->getValue('query_key');
    $this->configuration['redirect_path'] = $form_state->getValue('redirect_path');
    $this->configuration['reset'] = $form_state->getValue('reset');
    $this->configuration['placeholder_text'] = $form_state->getValue('placeholder_text');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = $this->formBuilder->getForm('\Drupal\rocketship_blocks\Form\SimpleSearchForm', $this->configuration);

    return $build;
  }

}
