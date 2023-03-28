<?php

namespace Drupal\rocketship_core\Plugin\Field\FieldFormatter;

use Drupal\Core\Entity\Element\EntityAutocomplete;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Template\Attribute;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'rs_configurable_link' formatter.
 *
 * @FieldFormatter(
 *   id = "rs_configurable_link",
 *   label = @Translation("Configurable link"),
 *   field_types = {
 *     "rs_display_field"
 *   }
 * )
 */
class ConfigurableLink extends FormatterBase {

  /**
   * The Token service.
   *
   * @var \Drupal\Core\Utility\Token
   */
  protected $token;

  /**
   * Provides a service for path aliases.
   *
   * @var \Drupal\path_alias\AliasManagerInterface
   */
  protected $aliasManager;

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
    $class->token = $container->get('token');
    $class->aliasManager = $container->get('path_alias.manager');
    $class->renderer = $container->get('renderer');
    return $class;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'link' => '',
      'link_text' => 'Back to overview',
      'link_class' => '',
      'query_string' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $config = $this->getSettings();

    $summary = [];
    $summary[] = 'Url: ' . $this->getSetting('link');
    $summary[] = 'Link text: ' . $this->getSetting('link_text');
    $summary[] = 'Class: ' . $this->getSetting('link_class');
    $summary[] = 'Query string: ' . $this->getSetting('query_string');

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $element = parent::settingsForm($form, $form_state);

    $element['link'] = [
      '#type' => 'entity_autocomplete',
      '#target_type' => 'node',
      '#process_default_value' => FALSE,
      '#title' => $this->t('URL'),
      '#default_value' => $this->getSetting('link'),
      '#description' => $this->t('Start typing the title of a piece of content to select it. You can also enter an internal path such as %add-node or an external URL such as %url. Enter %front to link to the front page. Token input is available, if token replacement will result with empty text link will not be rendered.', [
        '%front' => '<front>',
        '%add-node' => '/node/add',
        '%url' => 'http://example.com',
      ]),
      '#required' => TRUE,
      '#element_validate' => [[get_called_class(), 'validateUriElement']],
    ];
    $element['link_text'] = [
      '#title' => $this->t('Link text'),
      '#description' => $this->t('Text for the link.'),
      '#type' => 'textfield',
      '#default_value' => $this->getSetting('link_text'),
      '#required' => TRUE,
    ];
    $element['link_class'] = [
      '#title' => $this->t('Link class'),
      '#description' => $this->t('Classes separated with a comma.'),
      '#type' => 'textfield',
      '#default_value' => $this->getSetting('link_class'),
      '#required' => FALSE,
    ];
    $element['query_string'] = [
      '#title' => $this->t('Query String'),
      '#description' => $this->t('Add the query string which should be added to the link, eg: title=My Title&subject=[node:title]. Do NOT include the "?"'),
      '#type' => 'textfield',
      '#default_value' => $this->getSetting('query_string'),
      '#required' => FALSE,
    ];

    $element['tokens'] = [
      '#theme' => 'token_tree_link',
      '#token_types' => $this->fieldDefinition->getTargetEntityTypeId() == 'node' ? ['node'] : ['term'],
      '#show_restricted' => FALSE,
      '#dialog' => TRUE,
    ];

    return $element;
  }

  /**
   * Form element validation handler for the URL element of link widget.
   *
   * @param array $element
   *   Element.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state.
   * @param array $form
   *   Form.
   *
   * @see \Drupal\link\Plugin\Field\FieldWidget\LinkWidget
   */
  public static function validateUriElement(array $element, FormStateInterface $form_state, array $form) {
    $uri = static::getUserEnteredStringAsUri($element['#value']);

    // If getUserEnteredStringAsUri() mapped the entered value to a 'internal:'
    // URI , ensure the raw value begins with '/', '?' or '#'.
    // @todo '<front>' is valid input for BC reasons, may be removed by
    //   https://www.drupal.org/node/2421941
    if (parse_url($uri, PHP_URL_SCHEME) === 'internal' && !in_array($element['#value'][0],
        [
          '/',
          '?',
          '#',
        ], TRUE) && substr($element['#value'], 0, 7) !== '<front>') {
      $form_state->setError($element, t('Manually entered paths should start with /, ? or #.'));
      return;
    }
  }

  /**
   * Gets the user-entered string as a URI.
   *
   * @param string $string
   *   String.
   *
   * @return string
   *   The URI.
   *
   * @see \Drupal\link\Plugin\Field\FieldWidget\LinkWidget
   */
  protected static function getUserEnteredStringAsUri($string) {
    // By default, assume the entered string is an URI.
    $uri = trim($string);
    // Detect entity autocomplete string, map to 'entity:' URI.
    $entity_id = EntityAutocomplete::extractEntityIdFromAutocompleteInput($string);
    if ($entity_id !== NULL) {
      // @todo Support entity types other than 'node'. Will be fixed in
      //   https://www.drupal.org/node/2423093.
      $uri = 'entity:node/' . $entity_id;
    }
    // Detect a schemeless string, map to 'internal:' URI.
    elseif (!empty($string) && parse_url($string, PHP_URL_SCHEME) === NULL) {
      // @todo '<front>' is valid input for BC reasons, may be removed by
      //   https://www.drupal.org/node/2421941
      // - '<front>' -> '/'
      // - '<front>#foo' -> '/#foo'
      if (strpos($string, '<front>') === 0) {
        $string = '/' . substr($string, strlen('<front>'));
      }
      $uri = 'internal:' . $string;
    }

    return $uri;
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
    // Get the config.
    $config = $this->getSettings();
    $config['link'] = static::getUserEnteredStringAsUri($config['link']);

    // Tokenize link title.
    $link_text = $this->token->replace($config['link_text'], [$entity->getEntityTypeId() => $entity], ['clear' => TRUE]);

    if (!strlen($link_text)) {
      return [];
    }

    // Try to tokenize link URL.
    $link = $this->token->replace($config['link'], [$entity->getEntityTypeId() => $entity], ['clear' => TRUE]);

    if (empty($link)) {
      return [];
    }
    // Prepare the url.
    $url = Url::fromUri($link);

    // Extra check for cases when configured URL is a path alias,
    // then we need to get source path like /node/{nid} so it will nicely
    // work with different languages.
    $langcode = $entity->language()->getId();
    $source = $this->aliasManager->getPathByAlias($url->toString(), $langcode);
    if ($source) {
      $url = Url::fromUri($this::getUserEnteredStringAsUri($source));
    }

    // Check if we have classes available.
    if (!empty($config['link_class'])) {
      $url->setOption('attributes', ['class' => explode(',', $config['link_class'])]);
    }

    // Add the query string if available.
    if (!empty($config['query_string'])) {
      // Get it all parsed and translated.
      $query_array = $this->parseString($config['query_string'], $entity);
      $url->setOption('query', $query_array);
    }

    $template = <<<TWIG
<div {{ attributes }}>
    {{ content }}
</div>
TWIG;

    // Build the attributes.
    $attributes = new Attribute();
    $attributes->addClass('rs-configurable-link-formatter');

    $content = Link::fromTextAndUrl($this->t($link_text), $url)
      ->toRenderable();

    return [
      '#type' => 'inline_template',
      '#template' => $template,
      '#context' => [
        'attributes' => $attributes,
        'content' => $content,
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

  /**
   * Parses query string in CGI-compliant way.
   *
   * Also translates and tokenizes values.
   *
   * @param string $str
   *   String to parse into an array.
   * @param \Drupal\Core\Entity\FieldableEntityInterface $entity
   *   The entity.
   *
   * @return array
   *   Returned array.
   *
   * @see https://php.net/manual/en/function.parse-str.php#76792
   */
  protected function parseString($str, FieldableEntityInterface $entity) {
    $arr = [];
    $pairs = explode('&', $str);
    foreach ($pairs as $i) {
      list($name, $value) = explode('=', $i, 2);
      // Translate then tokenize value.
      $value = $this->token->replace(t($value), [$entity->getEntityTypeId() => $entity], ['clear' => TRUE]);
      if (isset($arr[$name])) {
        if (is_array($arr[$name])) {
          $arr[$name][] = $value;
        }
        else {
          $arr[$name] = [$arr[$name], $value];
        }
      }
      else {
        $arr[$name] = $value;
      }
    }
    return $arr;
  }

}
