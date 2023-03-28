<?php

namespace Drupal\menu_clickthrough\Menu;

use Drupal\Core\Menu\MenuLinkManagerInterface;
use Drupal\Core\Menu\MenuLinkTreeElement;
use Drupal\Core\Menu\MenuLinkTreeInterface;
use Drupal\Core\Menu\MenuTreeParameters;
use Drupal\menu_link_content\Entity\MenuLinkContent;
use Drupal\node\NodeInterface;
use Drupal\system\Entity\Menu;

/**
 * Class MenuTreeStorageMenuHelper.
 *
 * @package Drupal\menu_clickthrough\Menu
 */
class MenuTreeStorageMenuHelper implements MenuHelperInterface {

  /**
   * Drupal\Core\Menu\MenuLinkManagerInterface definition.
   *
   * @var \Drupal\Core\Menu\MenuLinkManagerInterface
   */
  protected $menuLinkManager;

  /**
   * Drupal\Core\Menu\MenuTreeStorageInterface definition.
   *
   * @var \Drupal\Core\Menu\MenuTreeStorageInterface
   */
  protected $menuLinkTree;

  /**
   * MenuTreeStorageMenuHelper constructor.
   *
   * @param \Drupal\Core\Menu\MenuLinkManagerInterface $menu_link_manager
   *   Menu link manager.
   * @param \Drupal\Core\Menu\MenuLinkTreeInterface $menu_link_tree
   *   Link tree.
   */
  public function __construct(MenuLinkManagerInterface $menu_link_manager, MenuLinkTreeInterface $menu_link_tree) {
    $this->menuLinkManager = $menu_link_manager;
    $this->menuLinkTree = $menu_link_tree;
  }

  /**
   * {@inheritdoc}
   */
  public function isClickthroughEnabled(Menu $menu) {
    return $menu->getThirdPartySetting('menu_clickthrough', 'clickthrough_enabled');
  }

  /**
   * {@inheritdoc}
   */
  public function getMenuClickthroughElement(Menu $menu, $menu_link_content = NULL) {
    // Check if the menu is enabled for clickthrough.
    if ($this->isClickthroughEnabled($menu)) {
      $default = NULL;
      $formats = filter_formats();
      $default_format = reset($formats)->id();
      $format = FALSE;
      if ($menu_link_content) {
        $default = $menu_link_content->get('menu_clickthrough_description')->value;
        $format = $menu_link_content->get('menu_clickthrough_description')->format;
      }

      return [
        '#type' => 'text_format',
        '#title' => t('Teaser'),
        '#description' => t('This text is used in menu clickthrough.'),
        '#default_value' => $default,
        '#format' => $format ?: $default_format,
      ];
    }
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function getMenuLinkFromNode(NodeInterface $node) {
    $menu_defaults = menu_ui_get_menu_link_defaults($node);
    if ($menu_defaults && isset($menu_defaults['entity_id'])) {
      $id = $menu_defaults['entity_id'];
      $menu_link_content = MenuLinkContent::load($id);
      if ($menu_link_content && $menu_link_content->isTranslatable()) {
        if (!$menu_link_content->hasTranslation($node->language()->getId())) {
          $menu_link_content = $menu_link_content->addTranslation($node->language()
            ->getId(), $menu_link_content->toArray());
        }
        else {
          $menu_link_content = $menu_link_content->getTranslation($node->language()
            ->getId());
        }
      }
      return $menu_link_content;
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getNodeEnabledMenu(NodeInterface $node) {
    $node_type = $node->type->entity;
    $type_menus = $node_type->getThirdPartySetting('menu_ui', 'available_menus', ['main']);
    if ($type_menus) {
      $menus = Menu::loadMultiple($type_menus);
      foreach ($menus as $key => $menu) {
        if ($this->isClickthroughEnabled($menu)) {
          return $menu;
        }
      }
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getMenuLinks() {
    $menu_links = [];

    $menus = Menu::loadMultiple();
    foreach ($menus as $key => $menu) {
      if ($this->isClickthroughEnabled($menu)) {
        // Load the tree.
        $menu_plugins = $this->menuLinkTree->load($menu->id(), new MenuTreeParameters());

        foreach ($menu_plugins as $plugin_id => $menu_plugin) {
          $this->getFullTree($menu_links, $plugin_id, $menu_plugin);
        }
      }
    }
    return $menu_links;
  }

  /**
   * Try to load the whole tree of a menu in an array.
   *
   * @param array $menu_links
   *   Menu links.
   * @param string $plugin_id
   *   plugin ID.
   * @param \Drupal\Core\Menu\MenuLinkTreeElement $item
   *   Menu item.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginException
   */
  protected function getFullTree(array &$menu_links,
                                 $plugin_id,
                                 MenuLinkTreeElement $item) {
    $menu_links[$plugin_id] = $this->menuLinkManager->createInstance($plugin_id);
    if ($item->hasChildren) {
      foreach ($item->subtree as $key => $child) {
        $this->getFullTree($menu_links, $key, $child);
      }
    }
  }

}
