<?php

namespace Drupal\rocketship_core\Plugin\Field\FieldFormatter;

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
 *   id = "rs_time_ago",
 *   label = @Translation("Created Time Ago"),
 *   field_types = {
 *     "rs_display_field"
 *   }
 * )
 */
class TimeAgoFormatter extends FormatterBase {

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  public $dateFormatter;

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
    $class->dateFormatter = $container->get('date.formatter');
    $class->renderer = $container->get('renderer');
    return $class;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'fallback_format' => 'd/m/Y',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $summary[] = 'fallback format: ' . $this->getSetting('fallback_format');

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::settingsForm($form, $form_state);

    $element['fallback_format'] = [
      '#title' => t('Fallback format'),
      '#description' => t('Enter a PHP date format to use as the fallback for when javascript is not available'),
      '#type' => 'textfield',
      '#default_value' => $this->getSetting('fallback_format'),
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
    $fallback = $this->getSetting('fallback_format');
    $iso = $this->dateFormatter->format(
      $entity->getCreatedTime(),
      'custom',
      'c'
    );
    $human_readable = $this->dateFormatter->format(
      $entity->getCreatedTime(),
      'custom',
      $fallback
    );
    $build['created_time_ago'] = [
      '#markup' => '<div class="created-time-ago" data-datetime="' . $iso . '">' . $human_readable . '</div>',
      '#attached' => [
        'library' => [
          'rocketship_core/posted_days_ago',
        ],
      ],
      '#cache' => [
        'contexts' => [
          'timezone',
        ],
      ],
    ];

    $template = <<<TWIG
<div {{ attributes }}>
    {{ content }}
</div>
TWIG;

    // Build the attributes.
    $attributes = new Attribute();
    $attributes->addClass('rs-configurable-link-formatter');

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
