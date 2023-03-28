<?php

namespace Drupal\rocketship_core\Plugin\Field\FieldFormatter;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Template\Attribute;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'rs_time_ago' formatter.
 *
 * @FieldFormatter(
 *   id = "rs_show_parent",
 *   label = @Translation("Show Parent Field"),
 *   field_types = {
 *     "rs_display_field"
 *   }
 * )
 */
class ShowParentFieldFormatter extends FormatterBase {

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $class = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $class->renderer = $container->get('renderer');
    return $class;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'parent_field' => '',
      'parent_view_mode' => 'default',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    if (!empty($this->getSetting('parent_field'))) {
      $summary[] = 'Showing parent field: ' . $this->getSetting('parent_field');
    }
    if (!empty($this->getSetting('parent_view_mode'))) {
      $summary[] = 'Using parent view mode: ' . $this->getSetting('parent_view_mode');
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::settingsForm($form, $form_state);

    $element['parent_field'] = [
      '#title' => t('Parent field'),
      '#type' => 'textfield',
      '#default_value' => $this->getSetting('parent_field'),
      '#required' => TRUE,
    ];

    $element['parent_view_mode'] = [
      '#title' => t('Parent view mode'),
      '#type' => 'textfield',
      '#default_value' => $this->getSetting('parent_view_mode'),
      '#required' => TRUE,
    ];

    return $element;
  }

  /**
   * Build a link.
   *
   * @param \Drupal\Core\Entity\FieldableEntityInterface $entity
   *   The entity.
   *
   * @return array
   *   Render array.
   */
  protected function build(FieldableEntityInterface $entity) {
    /** @var \Drupal\Core\Entity\ContentEntityInterface $parent */
    $parent = $this->getHighestLevelParentEntity($entity);

    $build = [];
    $cache_tags = $entity->getCacheTags();
    $cache_tags = Cache::mergeTags($cache_tags, $parent->getCacheTags());

    $field = $this->getSetting('parent_field');
    $view_mode = $this->getSetting('parent_view_mode');

    if ($parent->hasField($field)) {
      $build = $parent->get($field)->view($view_mode);
    }

    if (!isset($build['#cache']['tags'])) {
      $build['#cache']['tags'] = [];
    }

    $build['#cache']['tags'] = Cache::mergeTags($build['#cache']['tags'], $cache_tags);

    $template = <<<TWIG
<div {{ attributes }}>
    {{ content }}
</div>
TWIG;

    // Build the attributes.
    $attributes = new Attribute();
    $attributes->addClass('rs-show-parent-field-formatter');

    return [
      '#type' => 'inline_template',
      '#template' => $template,
      '#context' => [
        'attributes' => $attributes,
        'content' => $build,
      ],
    ];

  }

  /**
   * Get highest parent.
   *
   * Recursively fetches the parent entity until top is reached and then
   * returns that one.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Entity.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   Parent.
   */
  protected function getHighestLevelParentEntity(EntityInterface $entity) {
    if (method_exists($entity, 'getParentEntity')) {
      $parent = $entity->getParentEntity();
      if ($parent) {
        return $this->getHighestLevelParentEntity($parent);
      }

      // Empty parent, assume this level is fine.
      return $entity;
    }

    // Already highest level as far as we can tell.
    return $entity;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    foreach ($items as $delta => $item) {
      $entity = $items->getEntity();
      $build = $this->build($entity);

      $this->renderer->addCacheableDependency($build, $entity);

      $elements[0] = $build;
      return $elements;
    }

    return $elements;
  }

}
