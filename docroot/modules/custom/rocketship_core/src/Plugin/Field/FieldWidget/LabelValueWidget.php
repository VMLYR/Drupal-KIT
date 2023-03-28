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
 *   id = "label_value_widget",
 *   label = @Translation("Label:value widget"),
 *   field_types = {
 *     "label_value_field"
 *   }
 * )
 */
class LabelValueWidget extends WidgetBase implements ContainerFactoryPluginInterface {

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
      'placeholder' => '',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = [];

    $elements['size'] = [
      '#type' => 'number',
      '#title' => t('Size of textfields'),
      '#default_value' => $this->getSetting('size'),
      '#required' => TRUE,
      '#min' => 1,
    ];
    $elements['placeholder_label'] = [
      '#type' => 'textfield',
      '#title' => t('Placeholder label'),
      '#default_value' => $this->getSetting('placeholder label'),
      '#description' => t('Text that will be shown inside the field until a value is entered. This hint is usually a sample value or a brief description of the expected format.'),
    ];
    $elements['placeholder_value'] = [
      '#type' => 'textfield',
      '#title' => t('Placeholder label'),
      '#default_value' => $this->getSetting('placeholder value'),
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
    if (!empty($this->getSetting('placeholder_label'))) {
      $summary[] = t('Placeholder label: @placeholder', ['@placeholder' => $this->getSetting('placeholder')]);
    }
    if (!empty($this->getSetting('placeholder_value'))) {
      $summary[] = t('Placeholder value: @placeholder', ['@placeholder' => $this->getSetting('placeholder')]);
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element['value'] = $element + [
      '#type' => 'textfield',
      '#default_value' => isset($items[$delta]->value) ? $items[$delta]->value : NULL,
      '#size' => $this->getSetting('size'),
      '#placeholder' => $this->getSetting('placeholder_value'),
      '#maxlength' => $this->getFieldSetting('max_length'),
    ];
    $element['value']['#title'] = $this->t('Value');
    if (isset($element['value']['#title_display'])) {
      unset($element['value']['#title_display']);
    }
    $element['label'] = [
      '#title' => $this->t('Label'),
      '#type' => 'textfield',
      '#default_value' => isset($items[$delta]->label) ? $items[$delta]->label : NULL,
      '#size' => $this->getSetting('size'),
      '#placeholder' => $this->getSetting('placeholder_value'),
      '#maxlength' => $this->getFieldSetting('max_length'),
      '#weight' => -5,
    ];

    $element['promoted'] = [
      '#title' => $this->t('Visible on teasers'),
      '#type' => 'checkbox',
      '#default_value' => isset($items[$delta]->promoted) ? $items[$delta]->promoted : FALSE,
      '#weight' => -6,
      '#access' => $this->currentUser->hasPermission('promote label_value item'),
      '#cache' => ['contexts' => ['user.permissions']],
    ];

    return $element;
  }

}
