<?php

namespace Drupal\menu_clickthrough\Menu;

use Drupal\node\NodeInterface;
use Drupal\system\Entity\Menu;

/**
 * Interface MenuHelperInterface.
 *
 * @package Drupal\menu_clickthrough\Menu
 */
interface MenuHelperInterface {

  /**
   * Get menu links.
   *
   * @return \Drupal\Core\Menu\MenuLinkInterface[]
   *   List of links.
   */
  public function getMenuLinks();

  /**
   * Checks if the menu is enabled to support clickthrough.
   *
   * @param \Drupal\system\Entity\Menu $menu
   *   Menu.
   *
   * @return bool
   *   If menu is clickthrough enabled.
   */
  public function isClickthroughEnabled(Menu $menu);

  /**
   * Return a form api element for the menu clickthrough description field.
   *
   * @param \Drupal\system\Entity\Menu $menu
   *   Menu.
   * @param \Drupal\menu_link_content\MenuLinkContentInterface|null $menu_link_content
   *   Menu link.
   *
   * @return array
   *   Renderable array.
   */
  public function getMenuClickthroughElement(Menu $menu, $menu_link_content);

  /**
   * Get menu link from node.
   *
   * @param \Drupal\node\NodeInterface $node
   *   Node.
   *
   * @return \Drupal\menu_link_content\MenuLinkContentInterface|null
   *   Menu link.
   */
  public function getMenuLinkFromNode(NodeInterface $node);

  /**
   * Get menu for node.
   *
   * Returns the first menu enabled for menu clickthrough which is available
   * for this node type. The main menu will always have priority and is checked
   * first.
   *
   * @param \Drupal\node\NodeInterface $node
   *   Node.
   *
   * @return \Drupal\system\Entity\Menu|null
   *   Menu.
   */
  public function getNodeEnabledMenu(NodeInterface $node);

}
