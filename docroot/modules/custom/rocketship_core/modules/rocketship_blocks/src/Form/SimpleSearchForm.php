<?php

namespace Drupal\rocketship_blocks\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Drupal\Core\Config\ConfigManagerInterface;

/**
 * Class SimpleSearchForm.
 */
class SimpleSearchForm extends FormBase {

  /**
   * Symfony\Component\HttpFoundation\RequestStack definition.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Drupal\Core\Config\ConfigManagerInterface definition.
   *
   * @var \Drupal\Core\Config\ConfigManagerInterface
   */
  protected $configManager;

  /**
   * SimpleSearchForm constructor.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $request_stack
   *   Request stack.
   * @param \Drupal\Core\Config\ConfigManagerInterface $config_manager
   *   Config manager.
   */
  public function __construct(
    RequestStack $request_stack,
    ConfigManagerInterface $config_manager
  ) {
    $this->requestStack = $request_stack;
    $this->configManager = $config_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack'),
      $container->get('config.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'search_api_fulltext_facet_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, $configuration = NULL) {
    $query_key = $configuration['query_key'];
    $default = $this->requestStack->getCurrentRequest()->query->get($query_key, '');

    $form['search'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Search'),
      '#title_display' => 'hidden',
      '#weight' => '0',
      '#default_value' => strtolower($default),
      '#attributes' => [
        'placeholder' => $configuration['placeholder_text']
      ]
    ];
    $form['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Search'),
    ];

    if (!empty($configuration['reset'])) {
      $form['reset'] = [
        '#type' => 'submit',
        '#value' => $this->t('Reset'),
        '#submit' => ['::resetForm'],
      ];
    }

    $form['#cache']['contexts'][] = 'url';

    return $form;
  }

  /**
   * Reset the form.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public function resetForm(array &$form, FormStateInterface $form_state) {
    $configuration = $form_state->getBuildInfo()['args'][0];
    $value = NULL;

    $this->redirectForm($configuration, $value, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $configuration = $form_state->getBuildInfo()['args'][0];
    $value = strtolower($form_state->getValue('search'));

    $this->redirectForm($configuration, $value, $form_state);
  }

  /**
   * Set the redirect for the form.
   *
   * @param array $configuration
   *   The configuration.
   * @param string $value
   *   The value.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  protected function redirectForm(array $configuration,
                                  $value,
                                  FormStateInterface $form_state) {
    // We don't merge this into the already existing query parameters as
    // that's not supported by facets. If we did, we could end up in a
    // situation where facets are checked but no results are returned,
    // meaning the visitor can't uncheck the facets. Basically, you can
    // search and then filter that result down with facets, but you can't
    // filter the results down with facets and then search inside that result
    // set. Because facets doesn't support a scenario where there are zero
    // results with checked facets.
    $query_key = $configuration['query_key'];
    $redirect_to = $configuration['redirect_path'];

    $query_data = [$query_key => $value];
    if (empty($value)) {
      $query_data = [];
    }

    switch ($redirect_to) {
      case'<current>':
        $route_match = \Drupal::routeMatch();
        $url = Url::fromRouteMatch($route_match);
        $url->setOption('query', $query_data);
        break;

      case '<front>':
        $url = Url::fromRoute('<front>', [], ['query' => $query_data]);
        break;

      default:
        $url = Url::fromUserInput($redirect_to, ['query' => $query_data]);
        break;
    }

    $form_state->setRedirectUrl($url);
  }

}
