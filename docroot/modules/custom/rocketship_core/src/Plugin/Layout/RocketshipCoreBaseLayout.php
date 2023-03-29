<?php

namespace Drupal\rocketship_core\Plugin\Layout;

use Drupal\Component\Utility\Html;
use Drupal\Core\File\Exception\FileWriteException;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Layout\LayoutDefault;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginFormInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class RocketshipCoreBaseLayout.
 *
 * @package Drupal\rocketship_core\Plugin\Layout
 */
abstract class RocketshipCoreBaseLayout extends LayoutDefault implements PluginFormInterface, ContainerFactoryPluginInterface {

  /**
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * @var \Drupal\Core\Entity\EntityTypeManager|object|null
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = new static($configuration, $plugin_id, $plugin_definition);
    $instance->currentUser = $container->get('current_user');
    $instance->entityTypeManager = $container->get('entity_type.manager');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    $configuration = parent::defaultConfiguration();
    // Set default to layout title.
    $configuration['label'] = $this->getPluginDefinition()->getLabel();
    $configuration['classes'] = NULL;
    $configuration['bem-modifier'] = NULL;
    $configuration['minimal_styling'] = FALSE;
    $configuration['top_layout_spacing'] = '1x';
    $configuration['bottom_layout_spacing'] = '1x';
    $configuration['background_image'] = NULL;
    $configuration['background_color'] = '_none';
    $configuration['backgrounds_full'] = TRUE;
    return $configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form = parent::buildConfigurationForm($form, $form_state);

    $form['classes_wrapper'] = [
      '#type' => 'details',
      '#title' => $this->t('Classes'),
      '#open' => $this->configuration['minimal_styling'] || $this->configuration['classes'] || $this->configuration['bem-modifier'],
      '#access' => $this->currentUser->hasPermission('administer minimal styling on rocketship layouts'),
    ];

    $form['classes_wrapper']['minimal_styling'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Minimal styling'),
      '#description' => $this->t('Remove most of the default classes added to this layout for easier custom styling.'),
      '#default_value' => $this->configuration['minimal_styling'],
      '#access' => $this->currentUser->hasPermission('administer minimal styling on rocketship layouts'),
    ];
    $form['classes_wrapper']['classes'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Extra classes'),
      '#description' => $this->t('Add extra classes to the outermost section wrapper here, split classes by space.'),
      '#default_value' => $this->configuration['classes'],
      '#access' => $this->currentUser->hasPermission('administer minimal styling on rocketship layouts'),
    ];
    $form['classes_wrapper']['bem-modifier'] = [
      '#type' => 'textfield',
      '#title' => $this->t('BEM modifier'),
      '#description' => $this->t('Add a modifier, this will be used to build BEM classes on the nested divs in the layout'),
      '#default_value' => $this->configuration['bem-modifier'],
      '#access' => $this->currentUser->hasPermission('administer minimal styling on rocketship layouts'),
    ];

    $form['padding_wrapper'] = [
      '#type' => 'details',
      '#title' => $this->t('Padding'),
      '#open' => $this->configuration['top_layout_spacing'] != '1x' || $this->configuration['bottom_layout_spacing'] != '1x',
    ];

    $form['padding_wrapper']['top_layout_spacing'] = [
      '#type' => 'select',
      '#title' => $this->t('Layout padding top'),
      '#description' => $this->t('How much extra space to include at the top of this layout'),
      '#default_value' => $this->configuration['top_layout_spacing'],
      '#options' => [
        '0x' => $this->t('No space'),
        '1x' => $this->t('Default spacing'),
        'minimal' => $this->t('minimal spacing'),
        'medium' => $this->t('medium spacing'),
      ],
    ];

    $form['padding_wrapper']['bottom_layout_spacing'] = [
      '#type' => 'select',
      '#title' => $this->t('Layout padding bottom'),
      '#description' => $this->t('How much extra space to include at the bottom of this layout'),
      '#default_value' => $this->configuration['bottom_layout_spacing'],
      '#options' => [
        '0x' => $this->t('No space'),
        '1x' => $this->t('Default spacing'),
        'minimal' => $this->t('minimal spacing'),
        'medium' => $this->t('medium spacing'),
      ],
    ];

    $form['background_wrapper'] = [
      '#type' => 'details',
      '#title' => $this->t('Background'),
      '#open' => !$this->configuration['backgrounds_full'] || ($this->configuration['background_color'] !== '_none' && !empty($this->configuration['background_color'])) || $this->configuration['background_image'],
      '#weight' => 99,
    ];

    $form['background_wrapper']['backgrounds_full'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Full-width backgrounds'),
      '#default_value' => $this->configuration['backgrounds_full'],
      '#description' => $this->t('Check if you want the backgrounds to stretch the entire width of the site (only works if the site pages have no sidebars).'),
    ];

    $form['background_wrapper']['background_color'] = [
      '#type' => 'radios',
      '#options' => static::getBackgroundColors(),
      '#title' => $this->t('Background color'),
      '#default_value' => static::mapBackgroundColorNameToIdentifier($this->configuration['background_color']),
    ];

    $background_image_default = NULL;
    $background_image_uuid = $this->configuration['background_image'];
    if ($background_image_uuid) {
      $media = $this->entityTypeManager->getStorage('media')
        ->loadByProperties(['uuid' => $background_image_uuid]);
      if ($media) {
        $media = reset($media);
        $background_image_default = $media->id();
      }
    }

    $form['background_wrapper']['background_image'] = [
      '#type' => 'media_library',
      '#allowed_bundles' => ['image'],
      '#title' => $this->t('Background image'),
      '#description' => $this->t('Select a background image for this layout.'),
      '#default_value' => $background_image_default,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['classes'] = $form_state->getValue([
      'classes_wrapper',
      'classes',
    ], NULL);
    $this->configuration['bem-modifier'] = $form_state->getValue([
      'classes_wrapper',
      'bem-modifier',
    ], NULL);
    $this->configuration['minimal_styling'] = $form_state->getValue([
      'classes_wrapper',
      'minimal_styling',
    ], FALSE);
    $this->configuration['top_layout_spacing'] = $form_state->getValue([
      'padding_wrapper',
      'top_layout_spacing',
    ], '1x');
    $this->configuration['bottom_layout_spacing'] = $form_state->getValue([
      'padding_wrapper',
      'bottom_layout_spacing',
    ], '1x');
    // Store UUID for background image for ease of migrating.
    $image = $form_state->getValue([
      'background_wrapper',
      'background_image',
    ], NULL);
    if ($image) {
      $image = $this->entityTypeManager->getStorage('media')
        ->load($image);
      if ($image) {
        $image = $image->uuid();
      }
    }
    $this->configuration['background_image'] = $image;
    $color = $form_state->getValue([
      'background_wrapper',
      'background_color',
    ], '_none');
    if ($color !== '_none') {
      $color = explode('/', $color)[0];
    }
    $this->configuration['background_color'] = $color;

    $this->configuration['backgrounds_full'] = $form_state->getValue([
      'background_wrapper',
      'backgrounds_full',
    ], TRUE);

    parent::submitConfigurationForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function build(array $regions) {
    $build = parent::build($regions);

    // Add extra classes.
    $classes = explode(' ', $this->configuration['classes'] ?? '');
    foreach ($classes as $class) {
      $build['#attributes']['class'][] = $class;
    }

    // Add BEM modifier.
    if (isset($this->configuration['bem-modifier'])) {
      $re = '/(?:\s|_)+/';
      $bem_modifier = preg_replace($re, '-', $this->configuration['bem-modifier']);
      // $bem_modifier = Html::cleanCssIdentifier($this->configuration['bem-modifier']);
      $build['#attributes']['data-bem-modifier'][] = $bem_modifier;
    }

    // Add the plugin definition ID as class as well.
    $build['#attributes']['class'][] =
      'layout--' . Html::cleanCssIdentifier(
        $this->getPluginDefinition()->id()
      );

    $background_image_uuid = $this->configuration['background_image'];
    if ($background_image_uuid) {
      $media = $this->entityTypeManager->getStorage('media')
        ->loadByProperties(['uuid' => $background_image_uuid]);
      if ($media) {
        $media = reset($media);
        $view_mode = $this->entityTypeManager
          ->getViewBuilder('media')
          ->view($media, 'layout_builder_background');
        $build['layout_background_image'] = $view_mode;
      }
      // print with {{ content.layout_background_image }}
    }

    // eg. webadmin has no acces to certain options or output in Layout Builder
    // printing an HTML-class helps to show/hide some things in front-end
    if ($this->currentUser->hasPermission('administer minimal styling on rocketship layouts')) {
      $build['#attributes']['class'][] = 'layout--advanced';
    }
    else {
      $build['#attributes']['class'][] = 'layout--simple';
    }

    // Make the form settings available in the javascript, twig, … because we
    // need some of them to selectively add HTML classes or JS functions.
    $config = \Drupal::config('rocketship_core.settings');
    $cssColors = $config->get('css_colors');
    $cssStructural = $config->get('css_structural');

    // libraries specifically for the Layouts and Content Blocks settings (admin-facing)
    // eg. styling and functionality for color picker on Layout
    // eg. add some styling and JS for layout pickers on Block
    $build['#attached']['library'][] = 'rocketship_core/admin';

    // libraries specifically for the Layouts in front-end (visitor-facing) or LB preview (site-builder)
    $build['#attached']['library'][] = 'rocketship_core/layouts';

    // Only if colors css is chosen.
    if ($cssColors) {
      // Only add colors if they have been generated. (visitor-facing)
      $path = 'public://css/style.content-blocks.colors-new.min.css';
      if (!is_file($path)) {
        static::generateBackgroundColorsCSSFile();
      }
      $build['#attached']['library'][] = 'rocketship_core/layout_colors';
    }

    // Only if structural css is chosen. (visitor-facing)
    if ($cssStructural) {
      $build['#attached']['library'][] = 'rocketship_core/structural';
    }


    return $build;
  }

  /**
   * Callback function for the field_p_bg_color.
   *
   * Adds 2 default colors to the list and allows alters for new colors.
   */
  public static function getBackgroundColors() {
    $options = [];
    $options['_none'] = t('- None -');

    // Config form values for number of color variants set.
    $variants = \Drupal::config('rocketship_core.settings')
      ->get('color_variants');

    foreach ($variants as $idx => $values) {
      $name = $values['name'];
      $foregroundColor = $values['foreground'];
      $backgroundColor = $values['background'];

      // Clean up foreground.
      $fg = str_replace(
        ['#', '/', '_', ' '],
        ['', '', '-', '-',],
        $foregroundColor);
      $fg = strtolower($fg);
      // Clean up background.
      $bg = str_replace(
        ['#', '/', '_', ' '],
        ['', '', '-', '-',],
        $backgroundColor);
      $bg = strtolower($bg);
      // Add label and value for the bg color.
      // Will be made into a class + inline CSS.
      $options[$name . '/' . $fg . '/' . $bg] = $name . '/' . $foregroundColor . '/' . $backgroundColor;
    }

    $theme = \Drupal::configFactory()->get('system.theme')->get('default');
    $path = \Drupal::service('extension.list.theme')->getPath($theme) . '/' . "$theme.theme";
    if (is_file($path)) {
      require_once $path;
    }
    $function = "{$theme}_rocketship_layout_bg_color_options_list";
    if (function_exists($function)) {
      $function($options);
    }

    return $options;
  }

  /**
   * @param $name
   *
   * @return string
   */
  public static function mapBackgroundColorNameToIdentifier($name) {
    if (!$name || $name == '_none') {
      return '_none';
    }

    $colors = static::getBackgroundColors();

    if (strpos($name, '/')) {
      // BC: $name is still the entire identifier
      // Check to make sure it's present in available colors.
      if (in_array($name, array_keys($colors))) {
        return $name;
      }
      // Nope, invalid choice. So map its actual name.
      $name = explode('/', $name)[0];
    }

    foreach ($colors as $color => $label) {
      if (explode('/', $color)[0] === $name) {
        return $color;
      }
    }

    return '_none';
  }


  /**
   * Generates CSS file.
   *
   * Find the paragraph CSS file and replace the placeholders (if there are any)
   * for the variant name and colors write it to the CSS folder.
   */
  public static function generateBackgroundColorsCSSFile() {

    $settings = \Drupal::config('rocketship_core.settings');
    $variants = $settings->get('color_variants');
    $cssPath = 'public://css';
    $cssTemplatePath = \Drupal::service('extension.list.module')->getPath('rocketship_core') . '/css/style.content-blocks.colors.min.css';
    // Make an array of the Changed CSS to save.
    $finalCSS = '';

    foreach ($variants as $idx => $values) {
      // Get the CSS.
      $css = file_get_contents($cssTemplatePath);
      // Replace the placeholders with our values.
      $css = str_replace(
        [
          'replace_variant_name',
          'replace_foreground_color',
          'replace_background_color',
          'replace_link_color',
          'replace_hover_color',
        ],
        [
          $values['name'],
          $values['foreground'],
          $values['background'],
          $values['link'],
          $values['hover'],
        ],
        $css
      );

      $finalCSS .= ' ' . $css;
    }

    // Save new CSS to a file (create if doesn't exist yet)
    try {
      \Drupal::service('file_system')
        ->prepareDirectory($cssPath, FileSystemInterface::CREATE_DIRECTORY);
      \Drupal::service('file_system')
        ->saveData($finalCSS, $cssPath . '/style.content-blocks.colors-new.min.css', FileSystemInterface::EXISTS_REPLACE);
    } catch (FileWriteException $e) {
      \Drupal::messenger()->addError(t('The file could not be created.'));
    } catch (\Exception $e) {
      \Drupal::messenger()->addError('Error saving file: ' . $e->getMessage());
    }
  }

}
