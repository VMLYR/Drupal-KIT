<?php

namespace Drupal\rocketship_core\Plugin\Condition;

use Drupal\Core\Condition\ConditionPluginBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Node UUID' condition.
 *
 * @Condition(
 *   id = "node_uuid",
 *   label = @Translation("Node UUID"),
 *   context = {
 *     "node" = @ContextDefinition("entity:node", label = @Translation("Node"))
 *   }
 * )
 */
class NodeUuid extends ConditionPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The entity storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $entityStorage;

  /**
   * Creates a new NodeType instance.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $entity_storage
   *   The entity storage.
   * @param array $configuration
   *   The plugin configuration, i.e. an array with configuration values keyed
   *   by configuration option name. The special key 'context' may be used to
   *   initialize the defined contexts by setting it to an array of context
   *   values keyed by context names.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   */
  public function __construct(EntityStorageInterface $entity_storage, array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityStorage = $entity_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $container->get('entity_type.manager')->getStorage('node_type'),
      $configuration,
      $plugin_id,
      $plugin_definition
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {

    $form['node_uuid_selected'] = [
      '#title' => $this->t('Node UUID'),
      '#type' => 'textfield',
      // '#required' => TRUE,.
      '#default_value' => $this->configuration['node_uuid_selected'],
    ];

    return parent::buildConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $value = $form_state->getValue('node_uuid_selected');
    // Has to be NULL specifically so it matches static::defaultConfiguration.
    $this->configuration['node_uuid_selected'] = empty($value) ? NULL : $value;
    parent::submitConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function summary() {

    return $this->t('The node UUID is @uuid', ['@uuid' => $this->configuration['node_uuid_selected']]);

  }

  /**
   * {@inheritdoc}
   */
  public function evaluate() {
    // If no UUID was entered, return true.
    if (empty($this->configuration['node_uuid_selected']) && !$this->isNegated()) {
      return TRUE;
    }

    try {
      /** @var \Drupal\node\NodeInterface $node */
      $node = $this->getContextValue('node');
      if (!$node) {
        // We don't have access to the Node context.
        // If a UUID was entered but there's nothing to compare it against
        // it can't possibly match. So return FALSE.
        return FALSE;
      }
    }
    catch (\Exception $e) {
      return FALSE;
    }

    return $this->configuration['node_uuid_selected'] == $node->uuid();
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return ['node_uuid_selected' => NULL] + parent::defaultConfiguration();
  }

}
