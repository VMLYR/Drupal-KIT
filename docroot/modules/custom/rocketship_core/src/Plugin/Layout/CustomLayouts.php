<?php

/**
 * This file only exists to make it possible for custom layouts in RS themes
 * to use extra's like adding custom classes via the LB interface
 */

namespace Drupal\rocketship_core\Plugin\Layout;

use Drupal\Core\Form\FormStateInterface;

/**
 * Class CustomLayouts.
 *
 * @package Drupal\rocketship_core\Plugin\Layout
 */
class CustomLayouts extends RocketshipCoreBaseLayout {

  /**
   * {@inheritdoc}
   */
  protected function calculateRegions(array &$regions) {
    // Do we need anything here? Don't think so.
  }

}
