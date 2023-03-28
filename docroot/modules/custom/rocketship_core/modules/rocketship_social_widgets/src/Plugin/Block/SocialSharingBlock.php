<?php

namespace Drupal\rocketship_social_widgets\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\rocketship_social_widgets\SocialShareLinkManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Path\CurrentPathStack;
use Drupal\Core\Url;

/**
 * Provides a 'SocialSharingBlock' Block.
 *
 * @Block(
 *   id = "rocketship_social_widgets_block",
 *   admin_label = @Translation("Rocketship social widgets"),
 *   category = @Translation("Rocketship Blocks"),
 * )
 */
class SocialSharingBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * @var \Drupal\Core\Path\CurrentPathStack
   */
  protected $currentPath;

  /**
   * @var \Drupal\rocketship_social_widgets\SocialShareLinkManager
   */
  protected $socialShareLinksManager;

  /**
   * Construct.
   *
   * @param array $configuration
   *   A configuration array containing information about plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param string $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   Interface for a configuration object factory.
   * @param \Drupal\Core\Path\CurrentPathStack $currentPath
   *   Current path for the current request.
   * @param \Drupal\rocketship_social_widgets\SocialShareLinkManager $socialShareLinksManager
   *   The Social Share links manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition,
    ConfigFactoryInterface $configFactory, CurrentPathStack $currentPath,
    SocialShareLinkManager $socialShareLinksManager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->configFactory = $configFactory;
    $this->currentPath = $currentPath;
    $this->socialShareLinksManager = $socialShareLinksManager;
  }

  /**
   * {@inheritdoc}
   .*/
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('config.factory'),
      $container->get('path.current'),
      $container->get('rocketship_social_widgets.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    $config = $this->getConfiguration();

    // Add the social share link selection field
    $links = $this->socialShareLinksManager->getSocialShareLinks();
    $options = [];
    foreach ($links as $link) {
      $options[$link->id()] = $link->label();
    }
    $form['link_selection'] = [
      '#type' => 'checkboxes',
      '#title' => t('Included links'),
      '#description' => t('Select the Social Share Links to show in this block. All links will be used if none are selected.'),
      '#default_value' => (!empty($config['link_selection'])) ? $config['link_selection'] : [],
      '#options' => $options,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->setConfigurationValue('link_selection', $form_state->getValue('link_selection'));
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    // Fetch enabled links configuration.
    $config = $this->getConfiguration();
    $enabled_links = (!empty($config['link_selection'])) ? $config['link_selection'] : [];

    // Create the build
    $build = rocketship_social_widgets_build_links($this->socialShareLinksManager->getSocialShareLinks(), $enabled_links);
    $build['#cache']['contexts'] = ['url.path'];

    return $build;
  }

}
