<?php

namespace Drupal\rocketship_core\Event;

use Drupal\path_alias\PathAliasInterface;
use Drupal\Component\EventDispatcher\Event;

/**
 * Class PathAliasUpdateEvent.
 *
 * @package Drupal\rocketship_core\Event
 */
class PathAliasUpdateEvent extends Event {

  /**
   * Name of the event fired on updating a path alias entity.
   */
  const PATH_ALIAS_UPDATE = 'rocketship_core.path_alias.update';

  /**
   * The path alias entity.
   *
   * @var \Drupal\path_alias\PathAliasInterface
   */
  protected $entity;

  /**
   * PathAliasUpdateEvent constructor.
   *
   * @param \Drupal\path_alias\PathAliasInterface $entity
   *   The updated path alias entity.
   */
  public function __construct(PathAliasInterface $entity) {
    $this->entity = $entity;
  }

  /**
   * Get the updated path alias entity.
   *
   * @return \Drupal\path_alias\PathAliasInterface
   *   The path alias entity.
   */
  public function getEntity() {
    return $this->entity;
  }

}
