<?php

namespace Drupal\menu_clickthrough\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\WidgetBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Menu\MenuParentFormSelector;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\menu_clickthrough\Menu\MenuHelperInterface;
use Drupal\system\Entity\Menu;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'clickthrough_widget' widget.
 *
 * @FieldWidget(
 *   id = "clickthrough_widget",
 *   label = @Translation("Clickthrough widget"),
 *   field_types = {
 *     "clickthrough_field_type"
 *   }
 * )
 */
class ClickthroughWidget extends WidgetBase implements ContainerFactoryPluginInterface {

  /**
   * Var.
   *
   * @var \Drupal\Core\Menu\MenuParentFormSelector
   */
  protected $parentFormSelector;

  /**
   * Var.
   *
   * @var \Drupal\menu_clickthrough\Menu\MenuHelperInterface
   */
  protected $menuHelper;

  /**
   * ClickthroughWidget constructor.
   *
   * @param string $plugin_id
   *   Plugin ID.
   * @param mixed $plugin_definition
   *   Plugin Definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   Field definition.
   * @param array $settings
   *   Settings.
   * @param array $third_party_settings
   *   Third party settings.
   * @param \Drupal\Core\Menu\MenuParentFormSelector $parent_form_selector
   *   Form selector.
   * @param \Drupal\menu_clickthrough\Menu\MenuHelperInterface $menu_helper
   *   Menu helper.
   */
  public function __construct($plugin_id,
                              $plugin_definition,
                              FieldDefinitionInterface $field_definition,
                              array $settings,
                              array $third_party_settings,
                              MenuParentFormSelector $parent_form_selector,
                              MenuHelperInterface $menu_helper) {

    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $third_party_settings);

    $this->parentFormSelector = $parent_form_selector;
    $this->menuHelper = $menu_helper;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['third_party_settings'],
      $container->get('menu.parent_form_selector'),
      $container->get('menu_clickthrough.menu_helper')
    );
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return ['menu' => ''] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $value = isset($items[$delta]->menu) ? $items[$delta]->menu : ':';
    $element['menu'] = $this->parentFormSelector->parentSelectElement($value, '', $this->getClickthroughMenus());

    $element['menu']['#title'] = t('Menu root level');
    $element['menu']['#description'] = t('Select the root element which will be used as root level to display the menu overview.
<br>Only the first level items will be shown corresponding to the selected item. 
<br>Current page will show a menu overview of the first clickthrough enabled menu found.');

    return $element;
  }

  /**
   * Return the menu options available as an option.
   *
   * @return array
   *   Options.
   */
  protected function getClickthroughMenus() {
    $menus = Menu::loadMultiple();
    $options = [
      '' => t('- Select -'),
      'self' => t('Current page menu'),
    ];
    foreach ($menus as $key => $menu) {
      if ($this->menuHelper->isClickthroughEnabled($menu)) {
        $options[$menu->id()] = $menu->label();
      }
    }
    return $options;
  }

}
