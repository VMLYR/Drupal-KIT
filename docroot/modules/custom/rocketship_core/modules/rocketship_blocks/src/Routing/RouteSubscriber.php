<?php

namespace Drupal\rocketship_blocks\Routing;

use Drupal\Core\Routing\RouteSubscriberBase;
use Drupal\Core\Routing\RoutingEvents;
use Symfony\Component\Routing\RouteCollection;

/**
 * Override the controller for layout_builder.move_block.
 */
class RouteSubscriber extends RouteSubscriberBase {

  /**
   * {@inheritdoc}
   */
  public function alterRoutes(RouteCollection $collection) {

    if ($route = $collection->get('layout_builder.choose_block')) {
      $defaults = $route->getDefaults();
      $defaults['_controller'] = '\Drupal\rocketship_blocks\Controller\ChooseBlockController::build';
      $route->setDefaults($defaults);
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() : array {
    // Ensure we alter the controller after other modules
    $events[RoutingEvents::ALTER] = ['onAlterRoutes', -1110];
    return $events;
  }


}
