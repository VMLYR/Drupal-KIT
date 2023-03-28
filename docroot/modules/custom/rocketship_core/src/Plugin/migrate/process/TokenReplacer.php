<?php

namespace Drupal\rocketship_core\Plugin\migrate\process;

use Drupal\migrate\ProcessPluginBase;
use Drupal\migrate\MigrateExecutableInterface;
use Drupal\migrate\Row;

/**
 * Provides a 'TokenReplacer' migrate process plugin.
 *
 * @MigrateProcessPlugin(
 *  id = "token_replacer"
 * )
 */
class TokenReplacer extends ProcessPluginBase {

  /**
   * {@inheritdoc}
   */
  public function transform($value, MigrateExecutableInterface $migrate_executable, Row $row, $destination_property) {
    $token_service = \Drupal::token();
    return $token_service->replace($value);
  }

}
