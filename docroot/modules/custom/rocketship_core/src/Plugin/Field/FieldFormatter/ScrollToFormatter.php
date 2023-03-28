<?php

namespace Drupal\rocketship_core\Plugin\Field\FieldFormatter;

use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Template\Attribute;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'rs_scroll_to' formatter.
 *
 * @FieldFormatter(
 *   id = "rs_scroll_to",
 *   label = @Translation("Scroll To"),
 *   field_types = {
 *     "rs_display_field"
 *   }
 * )
 */
class ScrollToFormatter extends FormatterBase {

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
      'scroll_to_identifier' => '',
      'button_text' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];
    $summary[] = 'Scrolling to identifier: ' . $this->getSetting('scroll_to_identifier');
    $summary[] = 'Text: ' . $this->getSetting('button_text');

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::settingsForm($form, $form_state);

    $element['scroll_to_identifier'] = [
      '#title' => t('Scroll-to identifier'),
      '#description' => t('Enter whatever should be after the "#"'),
      '#type' => 'textfield',
      '#default_value' => $this->getSetting('scroll_to_identifier'),
      '#required' => TRUE,
    ];

    $element['button_text'] = [
      '#title' => t('Button text'),
      '#type' => 'textfield',
      '#default_value' => $this->getSetting('button_text '),
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
    $text = $this->getSetting('button_text');
    $identifier = $this->getSetting('scroll_to_identifier');

    $url = Url::fromUserInput('#' . $identifier);

    $template = <<<TWIG
<div {{ attributes }}>
    {{ content }}
</div>
TWIG;

    // Build the attributes.
    $attributes = new Attribute();
    $attributes->addClass('rs-scroll-to-formatter');

    $content = Link::fromTextAndUrl(t($text), $url)->toString();

    return [
      '#type' => 'inline_template',
      '#template' => $template,
      '#context' => [
        'attributes' => $attributes,
        'content' => $content,
      ],
      '#cache' => [
        'contexts' => [
          'languages',
        ],
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
