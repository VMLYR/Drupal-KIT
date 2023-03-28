<?php

namespace Drupal\rocketship_core\Plugin\Field\FieldFormatter;

use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Entity\TranslatableInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Template\Attribute;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'rs_canonical_link' formatter.
 *
 * @FieldFormatter(
 *   id = "rs_canonical_link",
 *   label = @Translation("Canonical link"),
 *   field_types = {
 *     "rs_display_field"
 *   }
 * )
 */
class CanonicalLink extends FormatterBase {

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $class = parent::create($container, $configuration, $plugin_id, $plugin_definition);
    $class->renderer = $container->get('renderer');
    $class->languageManager = $container->get('language_manager');
    return $class;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'link text' => 'Read more',
      'link class' => '',
      'wrapper' => 'div',
      'class' => '',
      'link' => 1,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $config = $this->getSettings();

    $summary = [];
    $summary[] = 'Link text: ' . $config['link text'];
    if (!empty($config['link class'])) {
      $summary[] = 'Link class: ' . $config['link class'];
    }
    if (!empty($config['wrapper'])) {
      $summary[] = 'Wrapper: ' . $config['wrapper'];
    }
    if (!empty($config['class'])) {
      $summary[] = 'Class: ' . $config['class'];
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::settingsForm($form, $form_state);

    $element['link text'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Link text'),
      '#default_value' => $this->getSetting('link text'),
    ];
    $element['link class'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Link class'),
      '#default_value' => $this->getSetting('link class'),
      '#description' => $this->t('Put a class on the link. Eg: btn btn-default'),
    ];
    $element['wrapper'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Wrapper'),
      '#default_value' => $this->getSetting('wrapper'),
      '#description' => $this->t('Eg: h1, h2, p'),
    ];
    $element['class'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Class'),
      '#default_value' => $this->getSetting('class'),
      '#description' => $this->t('Put a class on the wrapper. Eg: block-title'),
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
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  protected function build(FieldableEntityInterface $entity) {
    // Initialize output.
    $output = '';
    $entity_title = $entity->getTitle();

    if ($this->getSetting('link text')) {
      $output = $this->t($this->getSetting('link text'));
    }

    if (empty($output)) {
      return [];
    }

    $template = <<<TWIG
{% if wrapper %}
<{{ wrapper }}{{ attributes }}>
{% endif %}
{% if is_link %}
  {% set translated_read_more = 'Read more about' | t %}
  {% set read_more = translated_read_more  ~ ' ' ~ entity_title %}
  {{ link(output, entity_url, {'aria-label': read_more}) }}
{% else %}
  {{ output }}
{% endif %}
{% if wrapper %}
</{{ wrapper }}>
{% endif %}
TWIG;

    // Sometimes it can be impossible to make a link to the entity, because it
    // has no id as it has not yet been saved, e.g. when previewing an unsaved
    // inline entity form.
    $is_link = FALSE;
    $entity_url = NULL;
    if (!empty($entity->id())) {
      $is_link = !empty($this->getSetting('link'));
      $entity_url = $entity->toUrl();
      if (!empty($this->getSetting('link class'))) {
        $entity_url->setOption('attributes', ['class' => explode(' ', $this->getSetting('link class'))]);
      }
    }

    // Build the attributes.
    $attributes = new Attribute();
    if (!empty($this->getSetting('class'))) {
      $attributes->addClass($this->getSetting('class'));
    }
    $attributes->addClass('rs-canonical-link-formatter');

    return [
      '#type' => 'inline_template',
      '#template' => $template,
      '#context' => [
        'is_link' => $is_link,
        'wrapper' => !empty($this->getSetting('wrapper')) ? $this->getSetting('wrapper') : 'div',
        'attributes' => $attributes,
        'entity_url' => $entity_url,
        'entity_title' => $entity_title,
        'output' => $output,
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $elements = [];

    $entity = $items->getEntity();
    if ($entity instanceof TranslatableInterface) {
      $langcode = $this->languageManager->getCurrentLanguage()->getId();
      if ($entity->hasTranslation($langcode)) {
        $entity = $entity->getTranslation($langcode);
      }
    }

    foreach ($items as $delta => $item) {
      $build = $this->build($entity);

      $this->renderer->addCacheableDependency($build, $entity);

      $elements[0] = $build;
      return $elements;
    }

    return $elements;
  }

}
