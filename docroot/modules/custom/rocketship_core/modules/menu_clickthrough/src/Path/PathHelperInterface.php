<?php

namespace Drupal\menu_clickthrough\Path;

/**
 * Interface PathHelperInterface.
 *
 * @package Drupal\menu_clickthrough\Path
 */
interface PathHelperInterface {

  /**
   * Get array of urls.
   *
   * @return \Drupal\Core\Url[]
   *   Array of urls.
   */
  public function getUrls();

}
