<?php

namespace Drupal\rocketship_core\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'label_value_widget' widget.
 *
 * @FieldWidget(
 *   id = "title_description_widget",
 *   label = @Translation("Title:description widget"),
 *   field_types = {
 *     "title_description_field"
 *   }
 * )
 */
class TitleDescriptionWidget extends WidgetBase implements ContainerFactoryPluginInterface {

  /**
   * Drupal\Core\Session\AccountInterface definition.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * {@inheritdoc}
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, array $third_party_settings, AccountInterface $account) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);
    $this->currentUser = $account;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($plugin_id, $plugin_definition, $configuration['field_definition'], $configuration['settings'], $configuration['third_party_settings'], $container->get('current_user'));
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'size' => 60,
      'placeholder_description' => '',
      'placeholder_title' => '',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = [];

    $elements['size'] = [
      '#type' => 'number',
      '#title' => t('Size of textfield'),
      '#default_value' => $this->getSetting('size'),
      '#required' => TRUE,
      '#min' => 1,
    ];
    $elements['placeholder_title'] = [
      '#type' => 'textfield',
      '#title' => t('Placeholder title'),
      '#default_value' => $this->getSetting('placeholder_title'),
      '#description' => t('Text that will be shown inside the field until a value is entered. This hint is usually a sample value or a brief description of the expected format.'),
    ];
    $elements['placeholder_description'] = [
      '#type' => 'textfield',
      '#title' => t('Placeholder description'),
      '#default_value' => $this->getSetting('placeholder_description'),
      '#description' => t('Text that will be shown inside the field until a value is entered. This hint is usually a sample value or a brief description of the expected format.'),
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];

    $summary[] = t('Textfield size: @size', ['@size' => $this->getSetting('size')]);
    if (!empty($this->getSetting('placeholder_title'))) {
      $summary[] = t('Placeholder title: @placeholder', ['@placeholder' => $this->getSetting('placeholder_title')]);
    }
    if (!empty($this->getSetting('placeholder_description'))) {
      $summary[] = t('Placeholder description: @placeholder', ['@placeholder' => $this->getSetting('placeholder_description')]);
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {

    $element['title'] = $element + [
      '#title' => $this->t('Title'),
      '#type' => 'textfield',
      '#default_value' => isset($items[$delta]->title) ? $items[$delta]->title : NULL,
      '#size' => $this->getSetting('size'),
      '#placeholder' => $this->getSetting('placeholder_title'),
      '#maxlength' => $this->getFieldSetting('max_length'),
      '#weight' => -5,
    ];

    $element['description'] = [
      '#weight' => 5,
      '#type' => 'textarea',
      '#default_value' => isset($items[$delta]->description) ? $items[$delta]->description : NULL,
      '#placeholder' => $this->getSetting('placeholder_description'),
      '#title' => t('Description'),
    ];
    $element['title']['#title'] = $this->t('Title');
    if (isset($element['title']['#title_display'])) {
      unset($element['title']['#title_display']);
    }

    return $element;
  }

}
